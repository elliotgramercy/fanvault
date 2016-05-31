<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    public function venue()
    {
        return $this->belongsTo('App\Venue');
    }

    public function action_image()
    {
        return $this->hasOne('App\Image','venue_id','venue_id')->where('type','action')->orderBy('date_created','DESC');
    }

    public function pitchers(){
    	return $this->hasMany('App\Player')->where('position','P');
    }

    public function hitters(){
    	return $this->hasMany('App\Player')->where('position','!=','P');
    }
}
