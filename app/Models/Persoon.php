<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Representeert een persoon in het systeem.
 */
class Persoon extends Model
{
    use HasFactory;

    /**
     * De databasetabel geassocieerd met het model.
     * @var string
     */
    protected $table = 'Personen';

    /**
     * De primaire sleutel voor het model.
     * @var string
     */
    protected $primaryKey = 'persoon_id';

    /**
     * De naam van de "created at" timestamp kolom.
     * Laravel beheert deze kolom automatisch als $timestamps niet false is.
     * @var string
     */
    const CREATED_AT = 'aangemaakt_op';

    /**
     * De naam van de "updated at" timestamp kolom.
     * Laravel beheert deze kolom automatisch als $timestamps niet false is.
     * Deze kolom moet bestaan in de 'Personen' tabel.
     * @var string
     */
    const UPDATED_AT = 'gewijzigd_op';

    /**
     * De attributen die via mass assignment toegewezen mogen worden.
     * @var array<int, string>
     */
    protected $fillable = [
        'naam',
        'actief',
    ];

    /**
     * De attributen die naar specifieke native types gecast moeten worden.
     * @var array<string, string>
     */
    protected $casts = [
        'actief' => 'boolean',        // Cast 'actief' naar een boolean waarde
        'aangemaakt_op' => 'datetime', // Zorgt voor consistente datetime casting voor de custom timestamp
        'gewijzigd_op' => 'datetime',  // Zorgt voor consistente datetime casting voor de custom timestamp
    ];

    /**
     * De standaardwaarden voor attributen van het model.
     * @var array<string, mixed>
     */
    protected $attributes = [
        'actief' => true, // Nieuwe personen zijn standaard actief
    ];

    /**
     * Definieert de 'hasMany' relatie met het Transactie model.
     * Een persoon kan meerdere transacties hebben.
     */
    public function transacties()
    {
        // Foreign key 'persoon_id' in Transacties tabel refereert naar 'persoon_id' (local key) in Personen tabel.
        return $this->hasMany(Transactie::class, 'persoon_id', 'persoon_id');
    }
}