<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Auth;

trait HasUserstamps
{
    protected static function bootHasUserstamps(): void
    {
        static::creating(function ($model) {
            if (Auth::check() && $model->isFillable('created_by') && ! $model->created_by) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check() && $model->isFillable('updated_by')) {
                $model->updated_by = Auth::id();
            }
        });
    }
}