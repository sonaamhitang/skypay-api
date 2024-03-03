<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BaseModel extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Check if the model uses a UUID and if the 'id' field is empty
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }
}
