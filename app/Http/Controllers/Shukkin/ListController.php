<?php


namespace App\Http\Controllers\Shukkin;


use App\Http\Controllers\Controller;
use App\Repository\ListRepositoryInterface;
use App\Services\SlackService;
use Illuminate\Http\Request;

class ListController extends Controller
{
    protected $response;
    protected $notification;

    public function __construct(ListRepositoryInterface $response, SlackService $notification) {
        $this->response     = $response;
        $this->notification = $notification;
    }

    public function handle(Request $request) {
        $payload = $request->all();
        $responses = $this->response->workList($payload);
        $message = '';

        foreach ($responses as $response) {
            if($response['is_owner']) {
                $message .= $response['name'].'さんのシフト:'."\t".$response['start'].'-'.$response['end']."\n";
            }
            else {
                $message .= $response['date'].':'."\t".$response['start'].'-'.$response['end']."\n";
            }
        }
        $this->notification->send($message);
    }
}
