<?php


namespace App\Repository;
use App\Repository\ShukkinRepositoryInterface;
use App\Work;


class ShukkinRepository implements ShukkinRepositoryInterface
{
    public function notification(Request $request) {
        //TODO,曜日,1週間の間隔,テキストからの読み取り
        $work = new Work();
        

    }
}
