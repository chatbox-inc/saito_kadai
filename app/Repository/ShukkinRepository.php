<?php


namespace App\Repository;
use App\Repository\ShukkinRepositoryInterface;
use App\Work;
use Carbon\Carbon;



class ShukkinRepository implements ShukkinRepositoryInterface
{
    public function workTime(Request $request) {
        //Requestの分割
        var_dump($request);
        logger($request);
        $dt   = Carbon::now();
        $year = $dt->year;
        $requestDate = explode(" ", $request->text);
        $date = Carbon::parse($year.$requestDate[0]);
        $start_time = $requestDate[1];
        $end_time = $requestDate[2];
        $user_id = $request->user_id;
        logger($date);
        logger($start_time);
        logger($end_time);


        //働ける規定時間以上であればDBに保存しずに
        //errorメッセージを出す。

        //DBに保存
        $work = new Work();
        $work->user_id      = $user_id;
        $work->start_time   = $start_time;
        $work->end_time     = $end_time;
        $work->date         = $date;
        $work->save();

        //1週間、１ヶ月の働く時間
        $workListM  = $work->whereMonth('date', $dt->month)->orwhere('user_id', $user_id)->all();
        $monthTime = 0;
        $weekTime = 0;
        $weekNum = new Carbon('now');
        $weekNum = $weekNum->weekNumberInMonth;//今の週番号

        //今月の一ヶ月間取得するように
        foreach($workListM as $workM) {
            //一ヶ月
            $dayTime = new Carbon($workM->end_time);
            $start_time = new Carbon($workM->start_time);
            $dayTime->diffInMinutes($start_time);
            var_dump($dayTime);
            logger($dayTime);
            $monthTime += $dayTime;
            //今週
            if($start_time->weekNumberInMonth == $weekNum) {
                $weekTime += $dayTime;
            }
        }

        var_dump($weekTime);
        var_dump($monthTime);
        logger($weekTime);
        logger($monthTime);

        $response = [
          'weekTime'  => $weekTime,
          'monthTime' => $monthTime
        ];

        return $response;
    }
}
