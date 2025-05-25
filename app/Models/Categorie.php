<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Representeert een categorie waaronder dranken kunnen vallen.
 */
class Categorie extends Model
{
    use HasFactory;

    /**
     * De databasetabel geassocieerd met het model.
     * @var string
     */
    protected $table = 'Categorieen';

    /**
     * De primaire sleutel voor het model.
     * @var string
     */
    protected $primaryKey = 'categorie_id';

    /**
     * Geeft aan of het model timestamps (created_at, updated_at) moet bijhouden.
     * In dit geval uitgeschakeld.
     * @var bool
     */
    public $timestamps = false;

    /**
     * De attributen die via mass assignment toegewezen mogen worden.
     * @var array<int, string>
     */
    protected $fillable = [
        'naam',
    ];

    /**
     * Definieert de 'has many' relatie met het Drank model.
     * Een categorie kan meerdere dranken hebben.
     */
    public function dranken()
    {
        return $this->hasMany(Drank::class, 'categorie_id', 'categorie_id');
    }
}