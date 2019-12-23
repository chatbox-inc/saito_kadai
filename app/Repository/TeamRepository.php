<?php


namespace App\Repository;

use App\SlackTeam;

class TeamRepository
{
    //新しく追加するときのみ
    public function saveInfo($id, $name) {
        $query = new SlackTeam();
        $query->team_id = $id;
        $query->team_name = $name;
        $query->save();
    }

    //名前変更時に実行される。
    public function update($id, $name) {
        $query = new SlackTeam();
        $query->where('team_id', $id)
            ->update(['team_name' => $name]);
    }
}
