<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswAftersaleOrderGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_aftersale_order_goods', function (Blueprint $table) {
            $table->increments('id');
    
            $table->integer('user_id')->unsigned()->comment('用户id');
            $table->integer('goods_id')->unsigned()->comment('商品id');
            $table->integer('sku_id')->unsigned()->comment('规格id');
    
            $table->char('order_no', 20)->comment('订单号');
            $table->char('aftersale_no', 20)->comment('售后订单号');
            $table->integer('order_goods_id')->unsigned()->comment('订单商品ID');
    
            $table->json('snapshot')->comment('商品快照');
            $table->integer('count')->unsigned()->comment('退货数量');
    
            $table->decimal('total_amount', 10, 2)->unsigned()->comment('订单金额');
            $table->decimal('refund_amount', 10, 2)->default(0)->unsigned()->comment('退款金额');
    
            $table->index('user_id');
            $table->index('goods_id');
            $table->index('order_goods_id');
            $table->index('sku_id');
            $table->index('order_no');
            $table->index('aftersale_no');
    
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
        Schema::dropIfExists('xsw_aftersale_order_goods');
    }
}
