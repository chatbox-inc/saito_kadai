<?php

namespace App\Console\Commands;

use App\Repository\TeamRepository;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class UpdateTeam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:slackTeam';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update team information from slack';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //slackAppとのGET通信
        $client = new Client();
        $response = $client->request("GET", "https://slack.com/api/team.info?token=".env('SLACK_TOKEN'));
        $responseBody = json_decode($response->getBody()->getContents(), true);

        //取得できているかの確認
        var_dump($responseBody['team']) ;

        //responseから、特定のデータの取得
        $team = $responseBody['team'];
        $id = $team['id'];
        $name = $team['name'];

        //DBの内容更新
        $repo = new TeamRepository();
        $repo->update($id, $name);
    }
}
