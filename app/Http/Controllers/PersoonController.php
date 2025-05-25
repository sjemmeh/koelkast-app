<?php

namespace App\Http\Controllers;

use App\Models\Persoon;
use Illuminate\Http\Request;
// Illuminate\Validation\Rule; // Rule is niet gebruikt in deze specifieke code, kan verwijderd worden.

class PersoonController extends Controller
{
    /**
     * Toont een lijst van alle personen, gesorteerd op naam.
     * Bedoeld voor algemene weergave.
     */
    public function index()
    {
        $personen = Persoon::orderBy('naam')->get();
        return view('personen.index', ['personen' => $personen]);
    }

    /**
     * Toont de beheerpagina voor personen.
     * Haalt alle personen op om (de)activeren en andere beheertaken mogelijk te maken.
     */
    public function indexBeheer()
    {
        $personen = Persoon::orderBy('naam', 'asc')->get(); 
        return view('beheer.personen.index', compact('personen'));
    }

    /**
     * Slaat een nieuwe persoon op na validatie.
     * Naam en e-mailadres moeten uniek zijn.
     */
    public function storeBeheer(Request $request)
    {
        $validatedData = $request->validate([
            'naam' => 'required|string|max:255|unique:Personen,naam',
            'email' => 'nullable|email|max:255|unique:Personen,email',
        ], [
            'naam.required' => 'De naam is verplicht.',
            'naam.unique' => 'Deze naam bestaat al.',
            'email.email' => 'Voer een geldig e-mailadres in.',
            'email.unique' => 'Dit e-mailadres is al in gebruik.',
        ]);

        Persoon::create($validatedData);

        return redirect()->route('beheer.personen.index')
                         ->with('success', 'Persoon "' . $validatedData['naam'] . '" succesvol toegevoegd.');
    }

    /**
     * Verwijder een specifieke persoon.
     * Een persoon kan niet verwijderd worden als er nog transacties aan gekoppeld zijn.
     * Maakt gebruik van Route Model Binding.
     */
    public function destroyBeheer(Persoon $persoon) // Route Model Binding voor $persoon
    {
        // Controleer of de persoon transacties heeft.
        // Dit vereist een 'transacties' relatie in het Persoon model, bijv.:
        // public function transacties() { return $this->hasMany(Transactie::class); }
        if ($persoon->transacties()->exists()) {
            return redirect()->route('beheer.personen.index')
                             ->with('error', 'Kan persoon "' . $persoon->naam . '" niet verwijderen, er zijn transacties aan gekoppeld.');
        }

        try {
            $persoonNaam = $persoon->naam;
            $persoon->delete();
            return redirect()->route('beheer.personen.index')
                             ->with('success', 'Persoon "' . $persoonNaam . '" succesvol verwijderd.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Vang databasefouten op, bijvoorbeeld als de check op transacties (door een race condition o.i.d.)
            // niet voldoende was en een foreign key constraint de verwijdering alsnog blokkeert.
            // Log::error('Fout bij verwijderen persoon (beheer): ' . $e->getMessage()); // Optioneel: log de fout
            return redirect()->route('beheer.personen.index')
                             ->with('error', 'Fout bij verwijderen van persoon "' . $persoon->naam . '". Mogelijk door database restricties.');
        }
    }

    /**
     * Wijzigt de 'actief' status van een persoon.
     * Maakt gebruik van Route Model Binding.
     */
    public function toggleActief(Persoon $persoon) // Route Model Binding voor $persoon
    {
        $persoon->actief = !$persoon->actief;
        $persoon->save();

        $status = $persoon->actief ? 'actief' : 'inactief';
        return redirect()->route('beheer.personen.index')
                         ->with('success', 'Persoon "' . $persoon->naam . '" is nu ' . $status . '.');
    }
}