<?php


namespace App\Repository;
use App\Repository\ShukkinRepositoryInterface;
use App\Work;
use Carbon\Carbon;
use Exception;



class ShukkinRepository implements ShukkinRepositoryInterface
{
    public function workMessage($payload) {

        //slashcommandsからのデータを取得
        $now   = Carbon::now();
        $year = $now->year;
        $requestDate = explode(" ", $payload['text']);

        //入力値が間違えていた時の判定
        try {
            $date = Carbon::parse($year.$requestDate[0]);
            $start_time = Carbon::parse($year.$requestDate[0].$requestDate[1]);
            $end_time   = Carbon::parse($year.$requestDate[0].$requestDate[2]);
            $submitWeek = $start_time->weekNumberInMonth-1;
            $user_id = $payload['user_id'];
        } catch(Exception $e) {
            $response = [
                'status'   => -1,
            ];

            return $response;
        }

        $work = new Work();
        //過去に登録しているかどうか
        $response=$this->checkTime($date, $now);
        if($response['status']== 0) {
            return $response;
        }

        //人数の確認
        $response=$this->checkNum($work, $date);
        if($response['status']== 1) {
            return $response;
        }

        //一日働ける時間の確認
        //７時間までを前提にしている
        $shiftTime = $end_time->diffInMinutes($start_time);
        if($shiftTime > 7*60) {
            $response = [
                'status'   => 4,
            ];

            return $response;
        }

        $checker = $work->where('date', $date)->where('user_id', $user_id);
        //時間訂正したい時に同じ日のコマンドを打つと
        //週の限界時間をように超えるため訂正できなくなるのを防ぐ
        if(!$checker->exists()) {
            //働けるかの確認
            $response = $this->canWork($work, $user_id, $shiftTime, $now, $submitWeek);
            if ($response['status'] == 2) {
                return $response;
            }
        }else {
            $response = $this->canUpdate($work, $user_id, $shiftTime, $now, $submitWeek, $date);
            if ($response['status'] == 2) {
                return $response;
            }
        }

        //シフト残り時間の報告
        $response = $this->leftTime($work, $user_id, $start_time, $end_time, $date, $now);
        return $response;
    }


    ////////////////////////////////////////////////////////////////////////
    ////////////////日付け確認,人数確認、時間制限確認、シフト登録//////////////////
    public function checkTime($date, $now) {
        //申請された日付けが過去ではないか
        $result = 3;
        if($date <= $now) {
            $result = 0;
        }

        $response = [
            'status'   => $result,
        ];
        return $response;
    }

    public function checkNum($work, $date) {
        //人数が５人以上ではないかどうか
        $result = 3;
        $num = $work->where('date', $date)->count();

        if($num >=5) {
            $result = 1;
        }

        $response = [
            'status'   => $result,
        ];
        return $response;
    }

    public function canWork($work, $user_id, $shiftTime, $now, $submitWeek) {
        //働けるかどうか
        $result = 3;
        //１か月のシフトデータ
        $workListM  = $work->whereMonth('date', $now->month)->where('user_id', $user_id)->get();

        $weekLimit = 8*60;
        $monthLimit = 8*60*5;

        $weekTime = array(0, 0, 0, 0, 0);
        //各週のデータを取得する
        foreach($workListM as $workM) {
            $endTime = new Carbon($workM->end_time);
            $startTime = new Carbon($workM->start_time);
            $dayTime = $endTime->diffInMinutes($startTime);
            $weekTime[$startTime->weekNumberInMonth-1]  += $dayTime;
        }

        //シフト提出週の確認
        if($weekTime[$submitWeek]+$shiftTime > $weekLimit) {//8時間
            $result = 2;
        }
        //今月の確認
        if(array_sum($weekTime)+$shiftTime > $monthLimit) {
            $result = 2;
        }

        $weekTimeN = $weekTime[$submitWeek];
        $monthTime = array_sum($weekTime);

        $weekLeftTime = $weekLimit - $weekTimeN;
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
            'status'   =>  $result,
        ];
        return $response;
    }

    public function canUpdate($work, $user_id, $shiftTime, $now, $submitWeek, $date) {
        //働けるかどうか
        $result = 3;
        //１か月のシフトデータ
        $workListM  = $work->whereMonth('date', $now->month)->where('user_id', $user_id)->get();

        $weekLimit = 8*60;
        $monthLimit = 8*60*5;
        $subTime = 0;

        $weekTime = array(0, 0, 0, 0, 0);
        //各週のデータを取得する
        foreach($workListM as $workM) {
            $endTime = new Carbon($workM->end_time);
            $startTime = new Carbon($workM->start_time);
            if($date->toDateString() == $workM->date) {
                $dayTime = $shiftTime;
                $subTime = $shiftTime - $endTime->diffInMinutes($startTime);
            }else {
                $dayTime = $endTime->diffInMinutes($startTime);
            }
            $weekTime[$startTime->weekNumberInMonth-1]  += $dayTime;
        }

        //シフト提出週の確認
        if($weekTime[$submitWeek] > $weekLimit) {
            $weekTime[$submitWeek] -= $subTime;
            $result = 2;
        }
        //今月の確認
        if(array_sum($weekTime) > $monthLimit) {
            $result = 2;
        }

        $weekTimeN = $weekTime[$submitWeek];
        $monthTime = array_sum($weekTime)-$subTime;

        $weekLeftTime = $weekLimit - $weekTimeN;
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
            'status'   =>  $result,
        ];
        return $response;
    }

    public function leftTime($work, $user_id, $start_time, $end_time, $date, $now) {
        //DBに保存
        $checker = $work->where('user_id', $user_id)->where('date', $date);
        if($checker->exists()) {
            $checker->update(['start_time' => $start_time, 'end_time' => $end_time]);
        }else {
            $work->user_id      = $user_id;
            $work->start_time   = $start_time;
            $work->end_time     = $end_time;
            $work->date         = $date;
            $work->save();
        }

        //１か月のシフト
        $workListM  = $work->whereMonth('date', $now->month)->where('user_id', $user_id)->get();
        $weekNum = new Carbon('now');
        $weekNum = $weekNum->weekNumberInMonth;//今週の番号
        $weekLimit = 8*60;
        $monthLimit = 8*60*5;

        $weekTime = array(0, 0, 0, 0, 0);
        //各週のデータを取得する
        foreach($workListM as $workM) {
            $endTime = new Carbon($workM->end_time);
            $startTime = new Carbon($workM->start_time);
            $dayTime = $endTime->diffInMinutes($startTime);
            $weekTime[$startTime->weekNumberInMonth-1]  += $dayTime;
        }

        $weekTimeN = $weekTime[$weekNum-1];
        $monthTime = array_sum($weekTime);

        $weekLeftTime = $weekLimit - $weekTimeN;
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
            'status'   => 3,
        ];

        return $response;

    }

}
