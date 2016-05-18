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
}
