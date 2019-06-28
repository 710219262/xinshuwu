<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswVipSendLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_vip_send_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('用户ID');
            $table->decimal('price', 10, 2)->unsigned()->comment('vip价格');
            $table->enum('type', [
                'GUESS',
            ])->default('GUESS')->comment('类型');
            $table->enum('status', [
                'CREATED',
                'SENDED',
                'CANCELED'
            ])->default('CREATED')->comment('状态');
            $table->index(['user_id', 'status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_vip_send_log');
    }
}
