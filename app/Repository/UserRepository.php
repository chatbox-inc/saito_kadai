<?php


namespace App\Repository;


use App\SlackUser;

class UserRepository
{

    //追加と更新
    public function saveInfo($users) {
        foreach($users as $user) {
            //新規
            $query = new SlackUser();
            $query->slack_id = $user['id'];
            $query->team_id  = $user['team_id'];
            $query->name     = $user['name'];
            $query->is_owner = $user['is_owner'];

            if($user['is_owner'] == false) {
                //TODO
                //ここに8時間以上働く人かどうか判別する
                //slack_idを用いて
                $query->mode = 'バイト';
            }else {
                $query->mode = '管理者';
            }
            $query->save();

        }

    }
}
