<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserExpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_exp', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_goods_id')->unsigned()->comment('订单商品ID');
            $table->integer('user_id')->unsigned()->comment('发布体验的用户ID');
            $table->integer('sku_id')->unsigned()->comment('体验的商品规格ID');
            $table->integer('goods_id')->unsigned()->comment('体验的商品ID');
            $table->string('title')->default('')->comment('标题');
            $table->text('content')->default('')->comment('内容');
            $table->integer('view')->default(0)->unsigned()->comment('浏览量');
            $table->integer('like')->default(0)->unsigned()->comment('点赞量');
            $table->integer('collect')->default(0)->unsigned()->comment('收藏量');
            $table->integer('share')->default(0)->unsigned()->comment('分享量');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('goods_id');
            $table->index('title');
            $table->index('order_goods_id');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_user_exp');
    }
}
