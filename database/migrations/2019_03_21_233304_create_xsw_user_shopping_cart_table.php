<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserShoppingCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_shopping_cart', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('user_id')->unsigned()->comment('用户ID');
            $table->integer('store_id')->unsigned()->comment('店铺ID');
            $table->integer('goods_id')->unsigned()->comment('商品ID');
            $table->integer('sku_id')->unsigned()->comment('SKU ID');
            $table->mediumInteger('count')->default(1)->unsigned()->comment('购买数量');
            $table->enum('status', [
                'NORMAL',
                'INVALID',
                'SOLDOUT',
            ])->default('NORMAL')->comment('状态');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('store_id');
            $table->index('goods_id');
            $table->index('sku_id');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_user_shopping_cart');
    }
}
