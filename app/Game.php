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
        return $this->hasOne('App\Team','id','home_team_id');
    }

    public function away_team()
    {
        return $this->hasOne('App\Team','id','away_team_id');
    }

    public function home_team_no_players()
    {
        return $this->hasOne('App\Team','id','home_team_id');
    }

    public function away_team_no_players()
    {
        return $this->hasOne('App\Team','id','away_team_id');
    }

    public function home_team_scores(){
        return $this->hasOne('App\GamesScore');
    }

    public function away_team_scores(){
        return $this->hasOne('App\GamesScore');
    }

    public function home_team_lineup_pitchers(){
        return $this->hasMany('App\GamesLineup','team_id','home_team_id')->where('position_num',1)->with('player');
    }

    public function home_team_lineup_hitters(){
        return $this->hasMany('App\GamesLineup','team_id','home_team_id')->where('position_num','!=',1)->with('player');
    }

    public function away_team_lineup_pitchers(){
        return $this->hasMany('App\GamesLineup','team_id','away_team_id')->where('position_num',1)->with('player');
    }

    public function away_team_lineup_hitters(){
        return $this->hasMany('App\GamesLineup','team_id','away_team_id')->where('position_num','!=',1)->with('player');
    }

    public function attendees(){
        return $this->hasMany('App\GamesAttendee','game_id','id')->with('user_object');
    }

    public function friend_attendees(){
        return $this->hasMany('App\GamesAttendee','game_id','id')->with('user_object');
    }

    public function tailgates(){
        return $this->hasMany('App\Tailgate')->with('tags');
    }

    public function ticket(){
        return $this->hasOne('App\Ticket');
    }

    public function user_game_images(){
        return $this->hasMany('App\UserGameImage')->with('user');
    }

    public function user_game_crew_members(){
        return $this->hasMany('App\UserGameCrew');
    }

    public function invited_friends(){
        return $this->hasMany('App\GamesInvite');
    }

    public function home_team_game_stats()
    {
        return $this->hasMany('App\GamesPlayer','team_id','home_team_id')->with('player');
    }

    public function away_team_game_stats()
    {
        return $this->hasMany('App\GamesPlayer','team_id','away_team_id')->with('player');
    }

    public function officials(){
        return $this->hasMany('App\GamesOfficial','game_id','id')->with('official');
    }
}
