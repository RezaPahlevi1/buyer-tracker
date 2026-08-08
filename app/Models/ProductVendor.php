<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductVendor extends Pivot
{
    protected $table = 'product_vendor';

    protected $fillable = [
        'product_id',
        'vendor_id',
        'harga_dari_vendor',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'harga_dari_vendor' => 'decimal:2',
    ];
}