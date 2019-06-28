<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserOrderGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_order_goods', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('user_id')->unsigned()->comment('用户id');
            $table->integer('goods_id')->unsigned()->comment('商品id');
            $table->integer('sku_id')->unsigned()->comment('规格id');
            
            $table->char('order_no', 20)->comment('订单号');
            
            $table->json('snapshot')->comment('商品快照');
            $table->integer('count')->unsigned()->comment('购买数量');
            
            $table->decimal('total_amount', 10, 2)->unsigned()->comment('总价');
            $table->decimal('pay_amount', 10, 2)->unsigned()->comment('成交价');
            $table->decimal('org_per_price', 10, 2)->unsigned()->comment('原单价');
            $table->decimal('pay_per_price', 10, 2)->unsigned()->comment('成交单价');
            
            $table->tinyInteger('status')->unsigned()->default(0)->comment('状态');
            
            $table->index('user_id');
            $table->index('goods_id');
            $table->index('sku_id');
            $table->index('order_no');
            
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
        Schema::dropIfExists('xsw_user_order_goods');
    }
}
