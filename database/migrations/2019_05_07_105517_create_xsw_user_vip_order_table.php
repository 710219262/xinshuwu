<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserVipOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_vip_order', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('用户ID');
            $table->char('order_no', 32)->unique()->comment('订单号');
            $table->decimal('price', 10, 2)->unsigned()->comment('vip价格');
            $table->decimal('pay_amount', 10, 2)->unsigned()->comment('支付金额');
            $table->enum('pay_method', ['ALIPAY', 'WECHAT'])->default('WECHAT')->comment('支付方式');

            $table->enum('status', [
                'CREATED',
                'PAYED',
                'CANCELED'
            ])->default('CREATED')->comment('订单状态');

            $table->index(['user_id', 'status']);
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
        Schema::dropIfExists('xsw_user_vip_order');
    }
}
