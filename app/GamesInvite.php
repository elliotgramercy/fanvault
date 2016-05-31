<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamesInvite extends Model
{
    public function friend_user_obj(){
    	return $this->hasOne('App\User','id','friend_id');
    }
}
