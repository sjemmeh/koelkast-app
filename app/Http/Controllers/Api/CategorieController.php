<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategorieController extends Controller
{
    /**
     * Haalt alle categorieën op, gesorteerd op naam.
     */
    public function index()
    {
        $categorieen = Categorie::orderBy('naam')->get();
        return response()->json($categorieen);
    }

    /**
     * Slaat een nieuwe categorie op na validatie.
     * De naam van de categorie moet uniek zijn.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'naam' => [
                'required',
                'string',
                'max:100',
                Rule::unique('Categorieen', 'naam') 
            ]
        ]);

        $categorie = Categorie::create($validatedData);

        return response()->json([
            'message' => 'Categorie succesvol toegevoegd!',
            'categorie' => $categorie
        ], 201); // 201 Created
    }

    /**
     * Verwijder een specifieke categorie.
     * Een categorie kan alleen verwijderd worden als deze niet meer in gebruik is door dranken.
     */
    public function destroy(Categorie $categorie) // Maakt gebruik van Route Model Binding
    {
        // Controleer of de categorie nog gekoppeld is aan dranken.
        // Dit vereist een 'dranken' relatie in het Categorie model.
        if ($categorie->dranken()->exists()) {
            return response()->json([
                'message' => 'Kan categorie niet verwijderen: deze is nog in gebruik door een of meer dranken.'
            ], 422); // 422 Unprocessable Entity
        }

        $categorie->delete();

        return response()->json([
            'message' => 'Categorie succesvol verwijderd!'
        ]);
    }
}