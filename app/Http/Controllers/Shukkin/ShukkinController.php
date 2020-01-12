<?php

namespace App\Http\Controllers\Shukkin;

use App\Http\Controllers\Controller;
use App\Repository\ShukkinRepositoryInterface;
use Illuminate\Http\Request;
use App\Services\SlackService;

class ShukkinController extends Controller
{
      protected $response;
      protected $notification;

      public function __construct(ShukkinRepositoryInterface $response, SlackService $notification) {
          $this->response     = $response;
          $this->notification = $notification;
      }

     public function handle(Request $request) {
        $payload = $request->all();
        $response = $this->response->workMessage($payload);

        if($response['status'] == -1) {
            $message = '正しい入力値ではありません！'."\n".'以下のように入力してください！！！'."\n".'ex)/shukkin 0101 13:00 19:00';
        }
        else if($response['status'] == 0) {
            $message = '過去に登録はできません！';
        }
        else if($response['status'] == 1) {
            $message = '人数が５人に達しています！'."\n".'シフト登録できません！代表に連絡してください';
        }
        else if($response['status'] == 2) {
            $message = '申し込まれた時間では働けません!!'."\n".
                '今週の残り勤務可能時間は'.$response['weekHour'].'時間'.$response['weekMin'].'分です。'."\n".
                '今月の残り勤務可能時間は'.$response['monthHour'].'時間'.$response['monthMin'].'分です。';
        }
        else if($response['status'] == 3) {
            $message = '今週の残り勤務可能時間は'.$response['weekHour'].'時間'.$response['weekMin'].'分です。'."\n".
                '今月の残り勤務可能時間は'.$response['monthHour'].'時間'.$response['monthMin'].'分です。';
        }
        else if($response['status'] == 4) {
            $message = '1日に働ける時間は7時間です！';
        }

        $this->notification->send($message);
     }
}
