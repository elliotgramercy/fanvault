<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamesAttendee extends Model
{
    public function game_object()
    {
        return $this->hasOne('App\Game','id','game_id')->with('venue','home_team','away_team');
    }

    public function user_object(){
    	return $this->hasOne('App\User','id','user_id');
    }
}
