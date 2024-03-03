<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;


class Provider extends BaseModel
{
    use HasFactory;
    protected $guarded  = ['id'];
}
