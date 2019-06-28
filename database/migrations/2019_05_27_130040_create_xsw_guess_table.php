<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswGuessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_guess', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('stage_id')->unsigned()->comment('竞猜批次id');
            $table->integer('goods_id')->unsigned()->comment('商品id');
            $table->integer('user_id')->unsigned()->comment('用户id');
            $table->decimal('price', 8, 2)->unsigned()->comment('竞猜价格');
            $table->tinyInteger('is_create')->unsigned()->default(0)->comment('是否创建者');
            $table->tinyInteger('user_number')->unsigned()->default(1)->comment('人数统计');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_guess');
    }
}
