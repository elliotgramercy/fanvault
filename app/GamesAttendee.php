<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamesAttendee extends Model
{
    public function game_object()
    {
        return $this->hasOne('App\Game','id','game_id')->with('venue','home_team_no_players','away_team_no_players');
    }

    public function user_object(){
    	return $this->hasOne('App\User','id','user_id');
    }

}
