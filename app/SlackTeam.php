<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlackTeam extends Model
{
    public function slack_users() {
        return $this->hasMany('App\SlackUser');
    }
}
