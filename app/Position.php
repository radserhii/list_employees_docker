<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    public function users()
    {
        return $this->BelongsToMany('App\User');
    }

    public function department()
    {
        return $this->belongsTo('App\Department');
    }
}
