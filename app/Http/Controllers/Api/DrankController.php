<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Drank;
use App\Models\ProductBarcode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // Noodzakelijk voor database transacties
use Illuminate\Validation\ValidationException; // Voor het afhandelen van validatiefouten met een custom response

class DrankController extends Controller
{
    /**
     * Toont de details van een specifiek drankje, inclusief gerelateerde categorie en productbarcodes.
     */
    public function show(Drank $drank)
    {
        $drank->load('categorie', 'productBarcodes'); // Eager load relaties voor efficiëntie
        return response()->json($drank);
    }

    /**
     * Werkt een bestaand drankje bij, inclusief de hoofdbarcode en additionele productbarcodes.
     * Barcodes worden gecontroleerd op globale uniekheid.
     * De operaties worden binnen een database transactie uitgevoerd.
     */
    public function update(Request $request, Drank $drank)
    {
        $validatedRequestData = $request->validate([
            'naam_drank' => 'required|string|max:255',
            'barcode' => [ // De (nieuwe) hoofdbarcode voor de 'Dranken' tabel
                'nullable',
                'string',
                'max:255',
                Rule::unique('Dranken', 'barcode')->ignore($drank->drank_id, 'drank_id'),
            ],
            'additional_barcodes' => 'nullable|array',
            'additional_barcodes.*' => 'nullable|string|max:255', // Elke additionele barcode
            'categorie_id' => 'required|exists:Categorieen,categorie_id',
            'prijs' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'tht_datum' => 'required|date_format:Y-m', // Formaat Jaar-Maand
        ], [
            // Custom validatieberichten
            'naam_drank.required' => 'Naam is verplicht.',
            'barcode.unique' => 'Deze hoofdbarcode is al in gebruik (Dranken tabel).',
            'additional_barcodes.*.string' => 'Extra barcodes moeten tekst zijn.',
            'categorie_id.required' => 'Categorie is verplicht.',
            'prijs.required' => 'Prijs is verplicht.',
            'tht_datum.required' => 'THT-datum is verplicht.',
        ]);

        $newMainBarcode = $request->input('barcode');
        $additionalBarcodesInput = $request->input('additional_barcodes', []);

        // Verzamel en normaliseer alle ingediende barcodes (hoofd + additioneel)
        $submittedBarcodeValues = collect([$newMainBarcode])
            ->concat($additionalBarcodesInput)
            ->map(fn ($bc) => is_string($bc) ? trim($bc) : null)
            ->filter() // Verwijder null of lege strings
            ->unique()
            ->values();

        // Controleer of *nieuwe* of *gewijzigde* barcodes conflicteren met barcodes van *andere* producten.
        $currentProductBarcodesForThisDrank = $drank->productBarcodes()->pluck('barcode_value')->all();
        $barcodesToCheckGlobally = $submittedBarcodeValues->diff($currentProductBarcodesForThisDrank)->all();

        if (!empty($barcodesToCheckGlobally)) {
            $conflictingGlobalBarcodes = ProductBarcode::whereIn('barcode_value', $barcodesToCheckGlobally)
                                                     ->where('drank_id', '!=', $drank->drank_id) // Moet van een ander drankje zijn
                                                     ->pluck('barcode_value')->all();
            if (!empty($conflictingGlobalBarcodes)) {
                throw ValidationException::withMessages([
                    'barcode' => 'Een of meerdere barcodes zijn al globaal in gebruik door andere producten: ' . implode(', ', $conflictingGlobalBarcodes),
                ]);
            }
        }

        // Logica voor het valideren/beheren van de hoofdbarcode in relatie tot de lijst van alle barcodes.
        // Als de expliciet ingevulde hoofdbarcode niet in de lijst van 'submittedBarcodeValues' zit,
        // wordt deze nu toegevoegd om consistentie te waarborgen. Een drankje kan ook zonder barcodes bestaan.
        if (empty(trim($newMainBarcode)) && $submittedBarcodeValues->isEmpty()) {
            $validatedRequestData['barcode'] = null; // Zorg dat Dranken.barcode ook null wordt als geen barcodes gegeven
        } elseif (!empty(trim($newMainBarcode)) && !$submittedBarcodeValues->contains(trim($newMainBarcode))) {
            $submittedBarcodeValues->push(trim($newMainBarcode));
            $submittedBarcodeValues = $submittedBarcodeValues->unique()->values(); // Her-uniek maken voor de zekerheid
        }

        DB::beginTransaction();
        try {
            $drankDataToUpdate = $validatedRequestData;
            // Stel de hoofdbarcode in op basis van de (potentieel getrimde) input, of null als leeg.
            $drankDataToUpdate['barcode'] = !empty(trim($newMainBarcode)) ? trim($newMainBarcode) : null;
            // Voeg de dag toe aan tht_datum voor correcte opslag (YYYY-MM-DD).
            $drankDataToUpdate['tht_datum'] = $validatedRequestData['tht_datum'] . '-01';
            unset($drankDataToUpdate['additional_barcodes']); // Wordt apart gesynchroniseerd

            $drank->update($drankDataToUpdate);

            // Synchroniseer de product barcodes in de ProductBarcodes tabel.
            // 1. Verwijder barcodes die niet langer zijn toegewezen aan dit drankje.
            $barcodesToDelete = collect($currentProductBarcodesForThisDrank)->diff($submittedBarcodeValues)->all();
            if (!empty($barcodesToDelete)) {
                $drank->productBarcodes()->whereIn('barcode_value', $barcodesToDelete)->delete();
            }

            // 2. Voeg nieuwe barcodes toe of update bestaande (indien van toepassing, hier niet).
            // De globale uniekheidscheck hierboven heeft al conflicten met *andere* dranken afgevangen.
            foreach ($submittedBarcodeValues as $barcodeValue) {
                ProductBarcode::updateOrCreate(
                    ['drank_id' => $drank->drank_id, 'barcode_value' => $barcodeValue],
                    [] // Geen extra velden om te updaten als de combinatie al bestaat.
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                throw $e; // Her-werp de validatie exceptie zodat Laravel deze correct afhandelt
            }
            // Log andere excepties voor debugging. \Illuminate\Support\Facades\Log is nodig.
            // Log::error('Fout bij bijwerken drankje (API): ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Fout bij bijwerken van drankje: ' . $e->getMessage(), 'errors' => []], 500);
        }

        return response()->json([
            'message' => 'Drankje succesvol bijgewerkt!',
            'drank' => $drank->fresh()->load('categorie', 'productBarcodes') // Stuur de up-to-date data terug
        ]);
    }
}