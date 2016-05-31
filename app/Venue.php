<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
	public function teams()
    {
        return $this->hasMany('App\Team');
    }

    public function games()
    {
        return $this->hasMany('App\Game');
    }

    public function venue_image()
    {
        return $this->hasOne('App\Image', 'venue_id', 'id')->where('type','venue')->orderBy('created_at','DESC');
    }

    public function upcoming_games_for_venue(){
        $now = gmdate('Y-m-d H:i:s',strtotime('now'));
        $future = gmdate('Y-m-d H:i:s',strtotime('+30 days'));
        return $this->hasMany('App\Game')->where('scheduled','>',$now)->where('scheduled','<',$future);
    }
}
