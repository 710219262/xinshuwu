<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswGoodsImgTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_goods_img', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('goods_id')->unsigned()->comment('商品id');
            $table->string('url', 256)->comment('图片地址');
            $table->enum('type', ['BANNER', 'INFO'])->default('BANNER')->comment('图片类型:轮播,详情');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['goods_id', 'type']);
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_goods_img');
    }
}
