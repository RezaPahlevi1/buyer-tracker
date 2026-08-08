<?php

namespace App\Models;

use App\Models\Concerns\HasUserstamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    use HasFactory, HasUserstamps;

    protected $fillable = [
        'buyer_id',
        'product_id',
        'jumlah',
        'tanggal_beli',
        'harga_saat_beli',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_beli' => 'date',
        'harga_saat_beli' => 'decimal:2',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}