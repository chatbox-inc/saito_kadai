<?php


namespace App\Repository;
use App\Repository\ShukkinRepositoryInterface;
use App\Work;
use Carbon\Carbon;



class ShukkinRepository implements ShukkinRepositoryInterface
{
    public function workMessage($payload) {
        //TODO
        //正しい入力値しか受け取らないようにする。

        //slashcommandsからのデータを取得
        $dt   = Carbon::now();
        $year = $dt->year;
        $requestDate = explode(" ", $payload['text']);
        $date = Carbon::parse($year.$requestDate[0]);
        $start_time = Carbon::parse($year.$requestDate[0].$requestDate[1]);
        $end_time = Carbon::parse($year.$requestDate[0].$requestDate[2]);
        $user_id = $payload['user_id'];

        $work = new Work();
        //人数の確認
        $response=$this->checkNum($work, $date);
        if($response['canWork']== 1) {
            return $response;
        }
        //働けるかの確認
        $response=$this->canWork($work, $user_id, $start_time, $end_time, $dt);
        if($response['canWork']== 2) {
            return $response;
        }
        //シフト残り時間の報告
        $response = $this->leftTime($work, $user_id, $start_time, $end_time, $date, $dt);
        return $response;
    }


    public function checkNum($work, $date) {
        //人数が５人以上ではないかどうか
        $result = 3;
        $num = $work->whereMonth('date', $date)->count();
        if($num >=5) {
            $result = 1;
        }

        $response = [
            'canWork'   => $result,
        ];
        return $response;
    }

    public function canWork($work, $user_id, $start_time, $end_time, $dt) {
        //働けるかどうか
        $result = 3;
        //１か月のシフトデータ
        $workListM  = $work->whereMonth('date', $dt->month)->orwhere('user_id', $user_id)->get();
        $monthTime = 0;
        $weekTime = 0;
        $weekNum = new Carbon('now');
        $weekNum = $weekNum->weekNumberInMonth;//今週の番号
        $shiftTime = $end_time->diffInMinutes($start_time);
        $weekLimit = 8*60;
        $monthLimit = 8*60*5;

        //今月の一ヶ月間取得するように
        foreach($workListM as $workM) {
            //一ヶ月
            $endTime = new Carbon($workM->end_time);
            $startTime = new Carbon($workM->start_time);
            $dayTime = $endTime->diffInMinutes($startTime);
            $monthTime += $dayTime;
            if($monthTime+$shiftTime > $monthLimit) {//40時間
                $result = 2;
            }
            //今週
            if($startTime->weekNumberInMonth == $weekNum) {
                $weekTime += $dayTime;
                if($weekTime+$shiftTime > $weekLimit) {//8時間
                    $result = 2;
                }

            }
        }

        $weekLeftTime = $weekLimit - $weekTime;
        $monthLeftTime = $monthLimit - $monthTime;
        $weekHour  = $weekLeftTime / 60;
        $weekMin   = $weekLeftTime % 60;
        $monthHour = $monthLeftTime / 60;
        $monthMin  = $monthLeftTime % 60;

        $response = [
            'weekHour'  => $weekHour,
            'weekMin'   => $weekMin,
            'monthHour' => $monthHour,
            'monthMin'  => $monthMin,
            'canWork'   => $result,
        ];
        return $response;
    }

    public function leftTime($work, $user_id, $start_time, $end_time, $date, $dt) {
        //DBに保存
        $checker = $work->where('date', $date);
        if($checker->exsists()) {
            $checker->update(['start_time' => $start_time, 'end_time' => $end_time]);
        }else {
            $work->user_id      = $user_id;
            $work->start_time   = $start_time;
            $work->end_time     = $end_time;
            $work->date         = $date;
            $work->save();
        }

        //１か月のシフト
        $workListM  = $work->whereMonth('date', $dt->month)->orwhere('user_id', $user_id)->get();
        $monthTime = 0;
        $weekTime = 0;
        $weekNum = new Carbon('now');
        $weekNum = $weekNum->weekNumberInMonth;//今週の番号
        $weekLimit = 8*60;
        $monthLimit = 8*60*5;

        //今月の一ヶ月間取得するように
        foreach($workListM as $workM) {
            //一ヶ月
            $endTime = new Carbon($workM->end_time);
            $startTime = new Carbon($workM->start_time);
            $dayTime = $endTime->diffInMinutes($startTime);
            $monthTime += $dayTime;
            //今週
            if($startTime->weekNumberInMonth == $weekNum) {
                $weekTime += $dayTime;
            }
        }

        $weekLeftTime = $weekLimit - $weekTime;
        $monthLeftTime = $monthLimit - $monthTime;
        $weekHour  = $weekLeftTime / 60;
        $weekMin   = $weekLeftTime % 60;
        $monthHour = $monthLeftTime / 60;
        $monthMin  = $monthLeftTime % 60;

        $response = [
            'weekHour'  => $weekHour,
            'weekMin'   => $weekMin,
            'monthHour' => $monthHour,
            'monthMin'  => $monthMin,
            'canWork'   => 3,
        ];

        return $response;

    }

}
