<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    public function slack_user() {
        return $this->belongsTo('App\SlackUser', 'user_id', 'slack_id');
    }
}
