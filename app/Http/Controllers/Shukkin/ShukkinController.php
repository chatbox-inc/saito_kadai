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

        if($response['canWork']) {
            $message = '今週の残り勤務可能時間は'.$response['weekHour'].'時間'.$response['weekMin'].'分です。'
                .'今月の残り勤務時間は'.$response['monthHour'].'時間'.$response['monthMin'].'分です。';
        }
        else if($response['canWork'] == false) {
            $message = '申し込まれた時間では働けません!!'.'今週の残り勤務可能時間は'.$response['weekHour'].'時間'.$response['weekMin'].'分です。'
                .'今月の残り勤務可能時間は'.$response['monthHour'].'時間'.$response['monthMin'].'分です。';
        }
        $this->notification->send($message);
     }
}
