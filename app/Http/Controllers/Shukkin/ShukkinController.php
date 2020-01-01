<?php

namespace App\Http\Controllers\Shukkin;

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

        $message = $this->response->workTime($request);
        ///通知
        $this->notification->send($message);
    }

}
