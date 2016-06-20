<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamesLineup extends Model
{
    public function player(){
        return $this->hasOne('App\Player','id','player_id')->with('headshot');
    }
}
