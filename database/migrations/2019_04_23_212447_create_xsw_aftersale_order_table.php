<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswAftersaleOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_aftersale_order', function (Blueprint $table) {
            $table->increments('id');
            
            $table->char('aftersale_no', 20)->unique()->comment('售后单号');
            $table->integer('order_goods_id')->comment('退款退货订单商品ID');

            $table->integer('user_id')->unsigned()->comment('用户ID');
            $table->integer('store_id')->unsigned()->comment('店铺ID');
            
            $table->enum('status', [
                // 申请
                'REQUEST',
                // 拒绝
                'REJECTED',
                // 同意
                'AGREED',
                // 退货运输中
                'SHIPPING',
                // 商家确认收获
                'RECEIVED',
                // 退款处理中
                'PROCESSING',
                // 退款完成
                'COMPLETED',
                // 用户取消
                'CANCEL'
            ])->default('REQUEST')->comment('订单状态');
            
            $table->enum('type', [
                'REFUND',
                'RETURN_REFUND',
            ])->default('REFUND')->comment('售后类型');
            
            $table->decimal('refund_amount', 10, 2)->unsigned()->comment('退款金额');
            
            $table->string('receive_name')->default('')->comment('收货人姓名');
            $table->string('receive_phone')->default('')->comment('收货人电话');
            $table->string('receive_address')->default('')->comment('收货地址');
            $table->string('shipping_name')->default('')->comment('发货人姓名');
            $table->string('shipping_phone')->default('')->comment('发货电话');
            $table->string('shipping_address')->default('')->comment('发货地址');
            $table->string('reason')->default('')->comment('退款理由');
            $table->string('user_note')->default('')->comment('买家留言');
            $table->string('merchant_note')->default('')->comment('卖家留言');
            $table->json('images')->default('')->comment('凭证图片');

            $table->string('logistic_no', 64)->default('')->comment('退货物流单号');
            $table->string('logistic_company', 32)->default('')->comment('退货物流公司');
            $table->string('logistic_abbr', 32)->default('')->comment('退货物流公司缩写,查询接口使用');
            $table->text('logistic_info')->comment('退货物流信息');
            
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['store_id', 'status']);
            
            $table->index('aftersale_no');
            $table->index('logistic_no');
            $table->index('status');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_aftersale_order');
    }
}
