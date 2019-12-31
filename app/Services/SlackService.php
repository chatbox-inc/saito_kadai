<?php
namespace App\Services;

use Illuminate\Notifications\Notifiable;
use App\Notifications\SlackNotification;

class SlackService
{
    use Notifiable;

    public function send($message = null)
    {
        $this->notify(new SlackNotification($message));
    }

    protected function routeNotificationForSlack($notifiable)
    {
        return env('SLACK_URL');
    }

}
