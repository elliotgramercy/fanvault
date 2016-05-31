<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserGameImage extends Model
{
    public function user(){
        return $this->hasOne('App\User','id','user_id');
    }
}
