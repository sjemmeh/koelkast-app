<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Representeert een drankartikel in het systeem.
 */
class Drank extends Model
{
    use HasFactory;

    /**
     * De databasetabel geassocieerd met het model.
     * @var string
     */
    protected $table = 'Dranken';

    /**
     * De primaire sleutel voor het model.
     * @var string
     */
    protected $primaryKey = 'drank_id';

    /**
     * De naam van de "created at" timestamp kolom.
     * Standaard 'created_at'.
     * @var string
     */
    const CREATED_AT = 'toegevoegd_op';

    /**
     * De naam van de "updated at" timestamp kolom.
     * Standaard 'updated_at'.
     * @var string
     */
    const UPDATED_AT = 'gewijzigd_op';

    /**
     * De attributen die via mass assignment toegewezen mogen worden.
     * 'barcode' is hier de hoofdbarcode op de Dranken tabel zelf.
     * 'is_actief' bepaalt of het drankje beschikbaar is.
     * @var array<int, string>
     */
    protected $fillable = [
        'barcode',
        'naam_drank',
        'categorie_id',
        'prijs',
        'tht_datum',
        'is_actief',
    ];

    /**
     * De attributen die automatisch naar Carbon/DateTime objecten geconverteerd moeten worden.
     * Bevat ook de custom timestamp kolommen.
     * @var array<int, string>
     */
    protected $dates = [
        'tht_datum',
        'toegevoegd_op', // Custom created_at kolom
        'gewijzigd_op',  // Custom updated_at kolom
    ];

    /**
     * De standaardwaarden voor attributen van het model.
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_actief' => true, // Nieuwe dranken zijn standaard actief
    ];

    /**
     * De attributen die naar specifieke native types gecast moeten worden.
     * @var array<string, string>
     */
    protected $casts = [
        'prijs' => 'decimal:2',     // Cast prijs naar een decimaal getal met 2 cijfers na de komma
        'is_actief' => 'boolean',   // Cast is_actief naar een boolean waarde
        'tht_datum' => 'date:Y-m-d',// Standaard formattering voor tht_datum (ondanks $dates)
    ];

    /**
     * Definieert de 'belongsTo' relatie met het Categorie model.
     * Een drank behoort tot één categorie.
     */
    public function categorie()
    {
        // Foreign key 'categorie_id' in Dranken tabel refereert naar 'categorie_id' in Categorieen tabel.
        return $this->belongsTo(Categorie::class, 'categorie_id', 'categorie_id');
    }

    /**
     * Definieert de 'hasMany' relatie met het Transactie model.
     * Een drank kan in meerdere transacties voorkomen.
     */
    public function transacties()
    {
        // Foreign key 'drank_id' in Transacties tabel refereert naar 'drank_id' (local key) in Dranken tabel.
        return $this->hasMany(Transactie::class, 'drank_id', 'drank_id');
    }

    /**
     * Definieert de 'hasMany' relatie met het ProductBarcode model.
     * Een drank kan meerdere geassocieerde productbarcodes hebben (voor scannen).
     */
    public function productBarcodes()
    {
        // Foreign key 'drank_id' in product_barcodes tabel refereert naar 'drank_id' (local key) in Dranken tabel.
        return $this->hasMany(ProductBarcode::class, 'drank_id', 'drank_id');
    }
}