<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlackUser extends Model
{
    protected $table = 'slack_users';

    public function works() {
        return $this->hasMany('App\Work');
    }

    public function slack_team() {
        return $this->belongsTo('App\SlackTeam', 'team_id', 'team_id');
    }
}
