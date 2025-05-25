<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Representeert een specifieke productbarcode die gekoppeld is aan een Drank.
 * Hiermee kan een drank meerdere barcodes hebben.
 */
class ProductBarcode extends Model
{
    use HasFactory;

    /**
     * De databasetabel geassocieerd met het model.
     * @var string
     */
    protected $table = 'product_barcodes';

    /**
     * De primaire sleutel voor het model.
     * @var string
     */
    protected $primaryKey = 'barcode_id';

    /**
     * De attributen die via mass assignment toegewezen mogen worden.
     * @var array<int, string>
     */
    protected $fillable = [
        'drank_id',
        'barcode_value',
    ];

    // Dit model gebruikt Laravel's standaard timestamps ('created_at' en 'updated_at').
    // Er zijn geen custom timestamp-kolomnamen gedefinieerd.

    /**
     * Definieert de 'belongsTo' relatie met het Drank model.
     * Een productbarcode behoort tot één specifieke drank.
     */
    public function drank()
    {
        return $this->belongsTo(Drank::class, 'drank_id', 'drank_id');
    }
}