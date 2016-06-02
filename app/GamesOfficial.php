<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamesOfficial extends Model
{
    public function official(){
        return $this->hasOne('App\Official','id','official_id');
    }
}
