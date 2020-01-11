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
            $end_time = Carbon::parse($year.$requestDate[0].$requestDate[2]);
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
        $shiftTime = $end_time->diffInMinutes($start_time)/60;
        if($shiftTime > 7) {
            $response = [
                'status'   => 4,
            ];

            return $response;
        }

        $checker = $work->where('date', $date)->orwhere('user_id', $user_id);
        //時間訂正したい時に同じ日のコマンドを打つと
        //週の限界時間をように超えるため訂正できなくなるのを防ぐ
        if(!$checker->exists()) {
            //働けるかの確認
            $response = $this->canWork($work, $user_id, $shiftTime, $now);
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
        $num = $work->whereMonth('date', $date)->count();
        if($num >=5) {
            $result = 1;
        }

        $response = [
            'status'   => $result,
        ];
        return $response;
    }

    public function canWork($work, $user_id, $shiftTime, $now) {
        //働けるかどうか
        $result = 3;
        //１か月のシフトデータ
        $workListM  = $work->whereMonth('date', $now->month)->orwhere('user_id', $user_id)->get();
        $monthTime = 0;
        $weekTime = 0;
        $weekNum = new Carbon('now');
        $weekNum = $weekNum->weekNumberInMonth;//今週の番号

        logger($shiftTime);
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
                    logger('通ったよ');
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
            'status'   => $result,
        ];
        return $response;
    }

    public function leftTime($work, $user_id, $start_time, $end_time, $date, $now) {
        //DBに保存
        $checker = $work->where('date', $date);
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
        $workListM  = $work->whereMonth('date', $now->month)->orwhere('user_id', $user_id)->get();
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
            'status'   => 3,
        ];

        return $response;

    }

}
