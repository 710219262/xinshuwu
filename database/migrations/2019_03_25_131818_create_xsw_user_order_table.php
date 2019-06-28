<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_order', function (Blueprint $table) {
            $table->increments('id');
            
            $table->char('batch_no', 32)->comment('批量下单号');
            $table->char('order_no', 32)->unique()->comment('订单号');
            
            $table->integer('user_id')->unsigned()->comment('用户ID');
            $table->integer('store_id')->unsigned()->comment('店铺ID');
            
            $table->enum('status', [
                'CREATED',
                'PAYED',
                'SHIPPED',
                'RECEIVED',
                'COMPLETED',
                'SHARED',
                'RETURN_REQUEST',
                'RETURN_REJECTED',
                'RETURNING',
                'RETURNED',
                'REFUND_REQUEST',
                'REFUND_REJECTED',
                'REFUNDING',
                'REFUNDED',
                'EXCHANGE_REQUEST',
                'EXCHANGE_REJECTED',
                'EXCHANGED',
                'CANCELED',
            ])->default('CREATED')->comment('订单状态');
            
            $table->decimal('total_amount', 10, 2)->unsigned()->comment('订单总金额');
            $table->decimal('pay_amount', 10, 2)->unsigned()->comment('订单支付金额');
            $table->decimal('goods_price', 10, 2)->unsigned()->comment('商品价格');
            $table->decimal('delivery_price', 10, 2)->unsigned()->default(0)->comment('配送费');
            
            $table->enum('discount', ['YES', 'NO'])->default('NO')->comment('是否享受了折扣');
            
            $table->json('payload')->comment('请求体');
            
            $table->enum('pay_method', ['ALIPAY', 'WECHAT'])->default('WECHAT')->comment('支付方式');
            
            $table->json('address')->comment('收货地址');
            $table->string('shipping_address')->default('')->comment('发货地址');
            $table->string('note')->default('')->comment('买家留言');
            
            $table->string('logistic_no', 64)->default('')->comment('物流单号');
            $table->string('logistic_company', 32)->default('')->comment('物流公司');
            $table->string('logistic_abbr', 32)->default('')->comment('物流公司缩写,查询接口使用');
            $table->text('logistic_info')->comment('物流信息');
            
            $table->string('aff')->default('')->comment('推广码');
            $table->tinyInteger('is_deleted')->unsigned()->comment('用户软删');
            
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['store_id', 'status']);
            $table->index('batch_no');
            $table->index('logistic_no');
            $table->index('status');
            $table->index('aff');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_user_order');
    }
}
