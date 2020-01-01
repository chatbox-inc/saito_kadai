<?php


namespace App\Repository;


use App\SlackUser;

class UserRepository
{

    //追加と更新
    public function saveInfo($users) {
        foreach($users as $user) {
            $query = new SlackUser();//インスタンス作成
            $checker = $query->where('slack_id', $user['id']);//登録済みかの確認用変数

            if($checker->exists()) {
                //更新
                $checker->update(['name' => $user['name']]);
                //TODO
                //ここで、バイト・熟練者・管理者の設定をすべきか
                //slash commandで追加できるようにする方が楽

            }else {
                //新規追加
                $query->slack_id = $user['id'];
                $query->team_id  = $user['team_id'];
                $query->name     = $user['name'];
                //$query->is_owner = $user['is_owner'];
                $query->is_owner = false;
                $query->mode = 'バイト';
                //TODO
                //ここで、バイト・熟練者・管理者の設定をすべきか
                //slash commandで追加できるようにする方が楽
//                if($user['is_owner'] == false) {
//                    $query->mode = 'バイト';
//                }else {
//                    $query->mode = '管理者';
//                }

                $query->save();
            }
        }

    }
}
