<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tailgate extends Model
{
    public function tags(){
        return $this->hasMany('App\TailgatesTag')->with('tag')->select('tailgate_id','tag_id','id');
    }
}
