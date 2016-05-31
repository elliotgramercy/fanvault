<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TailgatesTag extends Model
{
    public function tag(){
        return $this->hasOne('App\Tag','id','tag_id')->select('id','value');
    }
}
