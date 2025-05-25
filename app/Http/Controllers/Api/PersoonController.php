<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Persoon;
use App\Models\Transactie;
use Illuminate\Http\Request;
use Carbon\Carbon; // Voor geavanceerde datumbewerkingen

class PersoonController extends Controller
{
    /**
     * Haalt transacties op voor een specifieke persoon binnen een bepaalde periode.
     * De transacties worden gesorteerd met de nieuwste eerst.
     * Maakt gebruik van Route Model Binding om de Persoon direct te laden.
     */
    public function getTransactiesVoorPeriode(Request $request, Persoon $persoon)
    {
        $validatedData = $request->validate([
            'start_datum' => 'required|date_format:Y-m-d',
            'eind_datum' => 'required|date_format:Y-m-d|after_or_equal:start_datum',
        ]);

        // Converteer datums naar Carbon instanties voor de query,
        // waarbij de volledige dag wordt meegenomen voor de start- en einddatum.
        $startDatum = Carbon::parse($validatedData['start_datum'])->startOfDay();
        $eindDatum = Carbon::parse($validatedData['eind_datum'])->endOfDay();

        $transacties = Transactie::where('persoon_id', $persoon->persoon_id)
                                 ->whereBetween('transactie_datum_tijd', [$startDatum, $eindDatum])
                                 ->orderBy('transactie_datum_tijd', 'desc') // Nieuwste transacties eerst
                                 ->get();

        return response()->json([
            'persoon' => $persoon, // De persoongegevens kunnen nuttig zijn voor de client
            'transacties' => $transacties,
        ]);
    }
}