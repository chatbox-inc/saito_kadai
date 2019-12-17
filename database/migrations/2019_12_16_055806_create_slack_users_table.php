<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlackUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slack_users', function (Blueprint $table) {
            $table->bigIncrements('id');//tableの主キー
            $table->string('slack_id');//slackのユーザーのid
            $table->string('team_id');//チームのid
            $table->string('name');//slackからとれる名前
            $table->boolean('is_owner');//管理者かどうかの判別
            $table->string('mode');//バイトで、8時間しか働けない者と、8時間以上働ける者を区別するため
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slack_users');
    }
}
