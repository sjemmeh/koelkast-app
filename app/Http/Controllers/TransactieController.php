<?php

namespace App\Http\Controllers;

use App\Models\Transactie;
use App\Models\Persoon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TransactieController extends Controller
{
    /**
     * Toont het transactieoverzicht in de beheerinterface.
     * Transacties worden gegroepeerd per persoon voor een geselecteerde maand (standaard de huidige maand).
     */
    public function indexBeheer(Request $request)
    {
        $geselecteerdeMaandInput = $request->input('maand_selectie'); // Input verwacht als "JJJJ-MM"
        $doelMaand = null;

        if ($geselecteerdeMaandInput) {
            try {
                // Probeer de input te parsen en zet de datum op het begin van de geselecteerde maand.
                $doelMaand = Carbon::createFromFormat('Y-m', $geselecteerdeMaandInput)->startOfMonth();
            } catch (\Exception $e) {
                // Bij een ongeldig formaat, log een waarschuwing en val terug op de huidige maand.
                Log::warning('Ongeldig maandformaat ontvangen voor transactieoverzicht: ' . $geselecteerdeMaandInput . '. Fout: ' . $e->getMessage());
                $doelMaand = Carbon::now()->startOfMonth();
            }
        } else {
            // Als er geen maand is geselecteerd, gebruik de huidige maand als standaard.
            $doelMaand = Carbon::now()->startOfMonth();
        }

        // Bepaal de start- en einddatum voor de query op basis van de doelmaand.
        $startDatumPeriode = $doelMaand->copy()->startOfMonth();
        $eindDatumPeriode = $doelMaand->copy()->endOfMonth();

        // Formatteer de periode voor weergave en voor het 'month' input veld.
        $periodeWeergave = $doelMaand->translatedFormat('F Y'); // bv. "Mei 2025"
        $geselecteerdeMaandVoorInput = $doelMaand->format('Y-m'); // bv. "2025-05"

        // Haal transactiegegevens op: totaalbedrag en aantal transacties per persoon voor de periode.
        $transactiesData = Transactie::whereBetween('transactie_datum_tijd', [$startDatumPeriode, $eindDatumPeriode])
            ->select(
                'persoon_id',
                DB::raw('SUM(prijs_ten_tijde_van_transactie) as totaal_bedrag'),
                DB::raw('COUNT(*) as aantal_transacties')
            )
            ->groupBy('persoon_id')
            ->get();

        // Verzamel persoon_id's uit de transactieresultaten en haal de bijbehorende Persoon modellen op.
        $persoonIds = $transactiesData->pluck('persoon_id')->filter()->unique();
        $personen = Persoon::whereIn('persoon_id', $persoonIds)->get()->keyBy('persoon_id');

        // Combineer transactieaggregaties met persoongsgegevens voor het overzicht.
        $overzichtPerPersoon = $transactiesData->map(function ($item) use ($personen) {
            $persoon = $personen->get($item->persoon_id);
            return (object) [
                'persoon_id' => $item->persoon_id,
                'persoon_naam' => $persoon ? $persoon->naam : 'Onbekende Persoon (ID: '.$item->persoon_id.')',
                'totaal_bedrag' => (float) $item->totaal_bedrag,
                'aantal_transacties' => (int) $item->aantal_transacties,
            ];
        })->sortByDesc('totaal_bedrag'); // Sorteer op hoogste totaalbedrag eerst.

        return view('beheer.transacties.index', [
            'overzichtPerPersoon' => $overzichtPerPersoon,
            'periode' => $periodeWeergave,
            'geselecteerdeMaandVoorInput' => $geselecteerdeMaandVoorInput,
            'startDatumPeriode' => $startDatumPeriode->toDateString(),
            'eindDatumPeriode' => $eindDatumPeriode->toDateString(),
        ]);
    }
}