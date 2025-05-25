<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Representeert een transactie in het systeem, zoals een aankoop.
 */
class Transactie extends Model
{
    use HasFactory;

    /**
     * De databasetabel geassocieerd met het model.
     * @var string
     */
    protected $table = 'Transacties';

    /**
     * De primaire sleutel voor het model.
     * @var string
     */
    protected $primaryKey = 'transactie_id';

    /**
     * Geeft aan of het model timestamps (created_at, updated_at) moet bijhouden.
     * Hier expliciet op true gezet, wat standaard gedrag is.
     * @var bool
     */
    public $timestamps = true;

    /**
     * De naam van de "created at" timestamp kolom.
     * Deze kolom wordt automatisch gevuld bij het aanmaken van een record.
     * @var string
     */
    const CREATED_AT = 'transactie_datum_tijd';

    /**
     * De naam van de "updated at" timestamp kolom.
     * Door deze op null te zetten, wordt het automatisch bijwerken van deze kolom uitgeschakeld.
     * @var string|null
     */
    const UPDATED_AT = null;

    /**
     * De attributen die automatisch naar Carbon/DateTime objecten geconverteerd moeten worden.
     * 'transactie_datum_tijd' is hier opgenomen voor explicietheid,
     * hoewel het als CREATED_AT kolom ook al als datum behandeld zou worden.
     * @var array<int, string>
     */
    protected $dates = ['transactie_datum_tijd'];

    /**
     * De attributen die via mass assignment toegewezen mogen worden.
     * @var array<int, string>
     */
    protected $fillable = [
        'persoon_id',
        'drank_id',
        'omschrijving_ten_tijde_van_transactie',
        'prijs_ten_tijde_van_transactie',
        'transactie_datum_tijd', // Kan ook automatisch gevuld worden indien niet meegegeven bij create
        'onbekende_barcode',
    ];

    /**
     * Definieert de 'belongsTo' relatie met het Persoon model.
     * Een transactie behoort tot één persoon.
     */
    public function persoon()
    {
        return $this->belongsTo(Persoon::class, 'persoon_id', 'persoon_id');
    }

    /**
     * Definieert de 'belongsTo' relatie met het Drank model.
     * Een transactie kan (optioneel) aan één drank gekoppeld zijn.
     */
    public function drank()
    {
        return $this->belongsTo(Drank::class, 'drank_id', 'drank_id');
    }
}