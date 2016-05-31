<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamesScore extends Model
{
    protected $casts = [
        'inning_scores' => 'array',
    ];
}
