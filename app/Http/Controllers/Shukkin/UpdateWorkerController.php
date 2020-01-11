<?php


namespace App\Http\Controllers\Shukkin;


use App\Http\Controllers\Controller;
use App\Repository\UpdateWorkerRepositoryInterface;
use App\Services\SlackService;
use Illuminate\Http\Request;

class UpdateWorkerController extends Controller
{
    protected $response;
    protected $notification;

    public function __construct(UpdateWorkerRepositoryInterface $response, SlackService $notification) {
        $this->response     = $response;
        $this->notification = $notification;
    }

    public function handle(Request $request) {
        $payload = $request->all();
        $message = $this->response->update($payload);
        $this->notification->send($message);
    }
}
