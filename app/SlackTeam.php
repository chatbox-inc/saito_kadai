<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlackTeam extends Model
{
    protected $table = 'slack_teams';

    public function slack_users() {
        return $this->hasMany('App\SlackUser');
    }
}
