<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FacebookLogin extends Model
{
    protected $primaryKey = 'user_id';

    public function user() {
        return $this->belongsTo(User::class);
    }
}
