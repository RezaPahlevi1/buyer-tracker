<?php

namespace App\Models;

use App\Models\Concerns\HasUserstamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasUserstamps;

    protected $fillable = [
        'nama_produk',
        'sku',
        'kategori',
        'deskripsi',
        'created_by',
        'updated_by',
    ];

    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class)
            ->using(ProductVendor::class)
            ->withPivot('harga_dari_vendor', 'is_active')
            ->withTimestamps();
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}