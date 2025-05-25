<?php

namespace App\Http\Controllers;

use App\Models\Drank;
use App\Models\Categorie;
use App\Models\ProductBarcode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // Voor database transacties
use Illuminate\Validation\ValidationException; // Om validatiefouten te genereren
use Illuminate\Support\Facades\Log; // Voor het loggen van fouten

class DrankController extends Controller
{
    /**
     * Toont de beheerpagina voor dranken, inclusief categorieën voor het toevoegformulier.
     * Relaties (categorie, productBarcodes) worden eager-loaded om N+1 queries te voorkomen.
     */
    public function indexBeheer()
    {
        $dranken = Drank::with(['categorie', 'productBarcodes'])
                          ->orderBy('naam_drank', 'asc')
                          ->get();
        $categorieen = Categorie::orderBy('naam', 'asc')->get(); // Voor selectie in formulieren

        return view('beheer.dranken.index', compact('dranken', 'categorieen'));
    }

    /**
     * Slaat een nieuwe drank op, inclusief de hoofdbarcode en eventuele additionele productbarcodes.
     * Voert controles uit op uniekheid van barcodes en gebruikt een database transactie.
     */
    public function storeBeheer(Request $request)
    {
        // Valideer de input, inclusief de hoofdbarcode en additionele barcodes
        $validatedRequestData = $request->validate([
            'naam_drank' => 'required|string|max:255',
            'barcode' => [ // Hoofdbarcode op de Dranken tabel
                'nullable',
                'string',
                'max:255',
                Rule::unique('Dranken', 'barcode'), // Moet uniek zijn in Dranken tabel
            ],
            'additional_barcodes' => 'nullable|array',
            'additional_barcodes.*' => 'nullable|string|max:255', // Validatie voor elk item in de array
            'categorie_id' => 'required|exists:Categorieen,categorie_id',
            'prijs' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'tht_datum' => 'required|date_format:Y-m', // Verwacht JJJJ-MM formaat
        ], [
            // Specifieke validatieberichten
            'naam_drank.required' => 'De naam van het drankje is verplicht.',
            'barcode.unique' => 'Deze hoofdbarcode is al in gebruik in de Dranken tabel.',
            'additional_barcodes.*.string' => 'Elke extra barcode moet een tekenreeks zijn.',
            'additional_barcodes.*.max' => 'Een extra barcode mag maximaal 255 tekens bevatten.',
            'categorie_id.required' => 'Selecteer een categorie.',
            'categorie_id.exists' => 'De geselecteerde categorie is ongeldig.',
            'prijs.regex' => 'De prijs moet een geldig bedrag zijn (bijv. 1.50 of 2).',
            'tht_datum.date_format' => 'De THT-datum moet in het formaat JJJJ-MM zijn.',
        ]);

        $mainBarcode = $request->input('barcode');
        $additionalBarcodesInput = $request->input('additional_barcodes', []);

        // Verzamel alle unieke, niet-lege ingediende barcodes (hoofd + additioneel)
        $allSubmittedBarcodes = collect([$mainBarcode])
            ->concat($additionalBarcodesInput)
            ->map(fn ($barcode) => is_string($barcode) ? trim($barcode) : null)
            ->filter() // Verwijder null of lege strings na trimmen
            ->unique()
            ->values()
            ->all();

        // Controleer of een van de ingediende barcodes al bestaat in de ProductBarcode tabel (globale check)
        if (!empty($allSubmittedBarcodes)) {
            $existingProductBarcodes = ProductBarcode::whereIn('barcode_value', $allSubmittedBarcodes)->pluck('barcode_value')->all();
            if (!empty($existingProductBarcodes)) {
                throw ValidationException::withMessages([
                    'barcode' => 'De volgende barcode(s) zijn al globaal in gebruik in het scansysteem: ' . implode(', ', $existingProductBarcodes),
                ]);
            }
        }

        DB::beginTransaction();
        try {
            // Bereid data voor het aanmaken van het Drank record
            $drankData = [
                'naam_drank' => $validatedRequestData['naam_drank'],
                'barcode' => !empty($mainBarcode) ? trim($mainBarcode) : null, // Hoofdbarcode (kan null zijn)
                'categorie_id' => $validatedRequestData['categorie_id'],
                'prijs' => $validatedRequestData['prijs'],
                'tht_datum' => $validatedRequestData['tht_datum'] . '-01', // Voeg dag toe voor opslag als YYYY-MM-DD
                'is_actief' => true, // Nieuwe dranken zijn standaard actief
            ];
            $drank = Drank::create($drankData);

            // Maak ProductBarcode records aan voor alle ingediende en gevalideerde barcodes
            if (!empty($allSubmittedBarcodes)) {
                foreach ($allSubmittedBarcodes as $barcodeValue) {
                    ProductBarcode::create([
                        'drank_id' => $drank->drank_id,
                        'barcode_value' => $barcodeValue,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('beheer.dranken.index')
                             ->with('success', 'Drankje "' . $drank->naam_drank . '" succesvol toegevoegd.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fout bij opslaan drankje en barcodes (beheer): ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());

            if ($e instanceof ValidationException) {
                throw $e; // Her-werp de validatie exceptie om de fouten correct te tonen
            }

            return redirect()->back()
                             ->with('error', 'Er is een fout opgetreden bij het opslaan: ' . $e->getMessage())
                             ->withInput();
        }
    }

    /**
     * Verwijder een drankje.
     * Controleert eerst of er nog transacties aan het drankje gekoppeld zijn.
     * Gerelateerde ProductBarcodes worden idealiter via onDelete('cascade') verwijderd.
     */
    public function destroyBeheer(Drank $drank) // Maakt gebruik van Route Model Binding
    {
        // Voorkom verwijdering als er nog transacties zijn voor dit drankje
        if ($drank->transacties()->exists()) {
            return redirect()->route('beheer.dranken.index')
                             ->with('error', 'Kan drankje "' . $drank->naam_drank . '" niet verwijderen, er zijn transacties aan gekoppeld.');
        }

        DB::beginTransaction();
        try {
            $drankNaam = $drank->naam_drank;

            // Indien ProductBarcodes niet via onDelete('cascade') in de database migratie worden verwijderd,
            // dient dit handmatig te gebeuren: $drank->productBarcodes()->delete();
            // Ga er hier vanuit dat de database foreign key constraint dit regelt.
            $drank->delete();
            
            DB::commit();

            return redirect()->route('beheer.dranken.index')
                             ->with('success', 'Drankje "' . $drankNaam . '" succesvol verwijderd.');
        } catch (\Illuminate\Database\QueryException $e) { // Vang specifiek database fouten op
            DB::rollBack();
            Log::error('Databasefout bij verwijderen drankje (beheer): ' . $e->getMessage());
            return redirect()->route('beheer.dranken.index')
                             ->with('error', 'Fout bij verwijderen van drankje "' . $drank->naam_drank . '". Details: ' . $e->getMessage());
        } catch (\Exception $e) { // Vang overige algemene fouten op
            DB::rollBack();
            Log::error('Algemene fout bij verwijderen drankje (beheer): ' . $e->getMessage());
            return redirect()->route('beheer.dranken.index')
                             ->with('error', 'Algemene fout bij verwijderen van drankje "' . $drank->naam_drank . '".');
        }
    }
}