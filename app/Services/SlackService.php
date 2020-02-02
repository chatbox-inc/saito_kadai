<?php
namespace App\Services;

use Illuminate\Notifications\Notifiable;
use App\Notifications\SlackShukkin;

class SlackService
{
    use Notifiable;

    public function send($message = null)
    {
        $this->notify(new SlackShukkin($message));
    }

    protected function routeNotificationForSlack($notifiable)
    {
        return env('SLACK_URL');
    }

}
