<?php

namespace App\Models;

use App\Models\Concerns\HasUserstamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Buyer extends Model
{
    use HasFactory, SoftDeletes, HasUserstamps;

    protected $fillable = [
        'nama',
        'no_hp',
        'alamat',
        'email',
        'catatan',
        'created_by',
        'updated_by',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function latestPurchase(): HasOne
    {
        return $this->hasOne(Purchase::class)->latestOfMany('tanggal_beli');
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