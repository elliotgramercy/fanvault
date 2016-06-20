<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    public function headshot(){
        return $this->hasOne('App\PlayersHeadshot','player_id','id')->where('size','250')->select('id','player_id','url');
    }

    public function player_lineup_position(){
    	return $this->hasOne('App\GamesLineup','player_id','id');
    }

    public function player_score(){
    	return $this->hasOne('App\GamesPlayer','player_id','id');
    }
}
