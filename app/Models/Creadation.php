<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Creadation extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'position',
    ];
}
