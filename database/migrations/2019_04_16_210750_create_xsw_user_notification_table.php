<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_notification', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('a_user_id')->unsigned()->default(0)->comment('action用户ID,0:系统');
            $table->integer('r_user_id')->unsigned()->comment('receiver用户ID');
            $table->integer('refer_id')->unsigned()->default(0)->comment('关联ID');
            $table->enum('jump', ['EXP', 'ARTICLE','USER'])->default('EXP')->comment('关联ID跳向哪里');
            
            $table->enum('action', [
                'LIKE',
                'CLT',
                'CMT',
                'RPL',
                'FLW',
                'SYS_ADT',
                'SYS_COM',
            ])->comment('动作类型');
            
            $table->string('target', 32)->default('')->comment('动作目标');
            
            $table->tinyInteger('is_read')->unsigned()->default(0)->comment('是否已读');
            $table->json('payload')->comment('冗余数据');
            
            $table->timestamps();
            
            $table->index('a_user_id');
            $table->index(['r_user_id', 'action']);
            $table->index('refer_id');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_user_notification');
    }
}
