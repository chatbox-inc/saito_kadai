<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('works', function (Blueprint $table) {
            $table->bigIncrements('id');//tableのid
            $table->string('user_id');//slack_userの情報
            $table->dateTime('start-time');//シフト勤務の開始時間
            $table->dateTime('end-time');//シフト勤務の終了時間
            $table->date('date');//シフトの日にちの取得
            $table->foreign('user_id')->references('slack_id')
                  ->on('slack_users')->onDelete('cascade');//slackのユーザのidとこのテーブルを関連付けしている
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('works');
    }
}
