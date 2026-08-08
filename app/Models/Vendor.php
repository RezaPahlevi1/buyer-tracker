<?php

namespace App\Models;

use App\Models\Concerns\HasUserstamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes, HasUserstamps;

    protected $fillable = [
        'nama_vendor',
        'nama_pic',
        'no_hp',
        'alamat',
        'email',
        'catatan',
        'created_by',
        'updated_by',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->using(ProductVendor::class)
            ->withPivot('harga_dari_vendor', 'is_active')
            ->withTimestamps();
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