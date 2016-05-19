<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
	public function venue()
    {
        return $this->belongsTo('App\Venue')->with('venue_image');
    }

    public function home_team()
    {
        return $this->hasOne('App\Team','id','home_team_id')->with('action_image');
    }

    public function away_team()
    {
        return $this->hasOne('App\Team','id','away_team_id')->with('action_image');
    }

    public function attendees(){
        return $this->hasMany('App\GamesAttendee','game_id','id')->with('user_object');
    }

    public function tailgates(){
        return $this->hasMany('App\Tailgate');
    }

    public function ticket(){
        return $this->hasOne('App\Ticket');
    }

    public function user_game_images(){
        return $this->hasMany('App\UserGameImage');
    }

    public function user_game_crew_members(){
        return $this->hasMany('App\UserGameCrew');
    }
}
