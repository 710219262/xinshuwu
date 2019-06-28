<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswGuessGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_guess_goods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('stage_id')->unsigned()->comment('竞猜批次id');
            $table->integer('goods_id')->unsigned()->comment('商品id');
            $table->string('goods_name', 64)->default('')->comment('商品名称');
            $table->string('goods_img', 64)->default('')->comment('商品名称');
            $table->tinyInteger('price_level')->unsigned()->default(0)->comment('价格位数');
            $table->tinyInteger('goods_orderby')->unsigned()->default(0)->comment('商品排序');
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
        Schema::dropIfExists('xsw_guess_goods');
    }
}
