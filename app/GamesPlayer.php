<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamesPlayer extends Model
{
    public function player(){
        return $this->hasOne('App\Player','id','player_id');
    }
}
