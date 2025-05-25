<?php

namespace App\Http\Controllers;

use App\Models\Drank;
use App\Models\Persoon;
use App\Models\Transactie;
use App\Models\ProductBarcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Toegevoegd omdat het in de finalizeTransaction gebruikt wordt

class KioskController extends Controller
{
    /**
     * Vooraf gedefinieerde prijzen voor producten zonder bekende barcode.
     * 'key' wordt gebruikt voor validatie (moet string zijn voor 'in' rule).
     * 'value' is de numerieke prijs voor opslag.
     * 'display' is voor weergave in de UI.
     */
    private $selectablePrices = [
        ['key' => '0.50', 'value' => 0.50, 'display' => '€ 0,50'],
        ['key' => '1.00', 'value' => 1.00, 'display' => '€ 1,00'],
        ['key' => '1.50', 'value' => 1.50, 'display' => '€ 1,50'],
        ['key' => '2.00', 'value' => 2.00, 'display' => '€ 2,00'],
        ['key' => '2.50', 'value' => 2.50, 'display' => '€ 2,50'],
    ];

    /**
     * Toont de hoofdinterface van de kiosk.
     * De weergave past zich aan op basis van de 'kiosk_state' in de sessie.
     */
    public function index()
    {
        $personen = Persoon::where('actief', true)->orderBy('naam', 'asc')->get();
        
        // Haal de huidige status en data van de kiosk op uit de sessie.
        $kioskState = session('kiosk_state', 'initial'); // Mogelijke staten: 'initial', 'confirm_drink', 'select_price'
        $kioskData = session('kiosk_data', []);

        $viewData = [
            'personen' => $personen,
            'kioskState' => $kioskState,
            'kioskData' => $kioskData,
            'selectablePrices' => [], // Initialiseer om undefined variable errors in view te voorkomen
        ];

        // Geef de selecteerbare prijzen alleen mee als de kiosk in de 'select_price' staat is.
        if ($kioskState === 'select_price') {
            $viewData['selectablePrices'] = $this->selectablePrices;
        }

        return view('kiosk.index', $viewData);
    }

    /**
     * Verwerkt een gescande barcode.
     * Zoekt naar een actieve drank via ProductBarcode.
     * Zet de kiosk-status naar 'confirm_drink' (indien gevonden) of 'select_price' (indien onbekend).
     */
    public function processBarcode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'barcode' => 'required|string|max:255',
        ], [
            'barcode.required' => 'Barcode is verplicht.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('kiosk.index')->withErrors($validator)->withInput();
        }

        $scannedBarcodeValue = $request->input('barcode');
        $drank = null;

        // Zoek eerst de barcode in de ProductBarcodes tabel.
        $productBarcode = ProductBarcode::where('barcode_value', $scannedBarcodeValue)->first();

        if ($productBarcode) {
            // Als de ProductBarcode gevonden is, haal de bijbehorende Drank op, maar alleen als deze actief is.
            $drank = Drank::where('drank_id', $productBarcode->drank_id)->where('is_actief', true)->first();
        }

        if ($drank) {
            // Drank gevonden en actief: ga naar bevestigingsscherm.
            session()->flash('kiosk_state', 'confirm_drink');
            session()->flash('kiosk_data', [
                'drank_id' => $drank->drank_id,
                'naam_drank' => $drank->naam_drank,
                'prijs' => $drank->prijs,
                'scanned_barcode' => $scannedBarcodeValue,
            ]);
        } else {
            // Drank niet gevonden of niet actief: laat gebruiker een prijs selecteren.
            session()->flash('kiosk_state', 'select_price');
            session()->flash('kiosk_data', [
                'scanned_barcode' => $scannedBarcodeValue, // Geef de gescande barcode mee voor context
            ]);
        }
        return redirect()->route('kiosk.index');
    }

    /**
     * Finaliseert de transactie na selectie van persoon en eventueel prijs.
     * Valideert de input dynamisch op basis van of een drank bekend was of een prijs handmatig is gekozen.
     */
    public function finalizeTransaction(Request $request)
    {
        $rules = [
            'persoon_id' => 'required|exists:Personen,persoon_id',
        ];
        $messages = [
            'persoon_id.required' => 'Selecteer alsjeblieft een persoon.',
        ];

        // Creëer een lijst van geldige prijs 'keys' voor de 'in:' validatieregel.
        $validPriceKeys = array_map(fn($priceInfo) => (string) $priceInfo['key'], $this->selectablePrices);

        if ($request->has('drank_id')) {
            // Scenario: drank was bekend en bevestigd.
            $rules['drank_id'] = 'required|exists:Dranken,drank_id';
        } elseif ($request->has('selected_price_key')) {
            // Scenario: drank was onbekend, prijs handmatig geselecteerd.
            $rules['selected_price_key'] = 'required|string|in:' . implode(',', $validPriceKeys);
            $rules['scanned_barcode_for_unknown'] = 'required|string'; // De barcode van het onbekende item
        } else {
            // Ongeldige poging, geen drank_id en geen selected_price_key.
            return redirect()->route('kiosk.index')->with('error', 'Ongeldige transactiepoging.');
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            // Zet de kiosk state en data terug zoals ze waren vóór de (mislukte) finalisatiepoging.
            // Dit is nodig omdat de state via flash-sessie data loopt.
            session()->flash('kiosk_state', $request->input('current_kiosk_state'));
            session()->flash('kiosk_data', json_decode($request->input('current_kiosk_data_json', '{}'), true) );
            return redirect()->route('kiosk.index')->withErrors($validator)->withInput();
        }

        $persoon = Persoon::find($request->input('persoon_id'));
        $transactieData = ['persoon_id' => $persoon->persoon_id];
        $successOmschrijving = '';

        if ($request->has('drank_id')) {
            // Verwerk transactie voor een bekende drank.
            $drank = Drank::find($request->input('drank_id'));
            $transactieData['drank_id'] = $drank->drank_id;
            $transactieData['omschrijving_ten_tijde_van_transactie'] = $drank->naam_drank;
            $transactieData['prijs_ten_tijde_van_transactie'] = $drank->prijs;
            $transactieData['onbekende_barcode'] = null;
            $successOmschrijving = $drank->naam_drank;
        } else {
            // Verwerk transactie voor een onbekende drank met handmatig gekozen prijs.
            $selectedPriceKey = $request->input('selected_price_key');
            $scannedBarcode = $request->input('scanned_barcode_for_unknown');
            
            $gekozenPrijsInfo = null;
            foreach ($this->selectablePrices as $priceInfo) {
                if ((string) $priceInfo['key'] === $selectedPriceKey) {
                    $gekozenPrijsInfo = $priceInfo;
                    break;
                }
            }

            if ($gekozenPrijsInfo) {
                $transactieData['omschrijving_ten_tijde_van_transactie'] = 'Onbekend (' . $scannedBarcode . ') - ' . $gekozenPrijsInfo['display'];
                $transactieData['prijs_ten_tijde_van_transactie'] = $gekozenPrijsInfo['value']; // De numerieke waarde
                $transactieData['drank_id'] = null;
                $transactieData['onbekende_barcode'] = $scannedBarcode;
                $successOmschrijving = 'Onbekend drankje (' . $scannedBarcode . ')';
            } else {
                // Zou niet bereikt moeten worden als validatie correct is.
                Log::error("Kon prijsinformatie niet vinden voor geselecteerde key: " . $selectedPriceKey . " voor barcode: " . $scannedBarcode);
                return redirect()->route('kiosk.index')->with('error', 'Fout bij verwerken van geselecteerde prijs.');
            }
        }

        Transactie::create($transactieData);

        $successMessage = $persoon->naam . ' heeft ' . $successOmschrijving .
                          ' gepakt voor €' . number_format($transactieData['prijs_ten_tijde_van_transactie'], 2, ',', '.');
        
        // Reset de kiosk state na een succesvolle transactie.
        session()->forget(['kiosk_state', 'kiosk_data']);
        return redirect()->route('kiosk.index')->with('success', $successMessage);
    }
}