<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_transaction', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('user_id')->unsigned()->comment('用户ID');
            $table->integer('share_id')->default(0)->unsigned()->comment('分享ID');
            $table->integer('order_id')->default(0)->unsigned()->comment('商品成交订单ID');

            $table->enum('action', [
                'INCOME',
                'WITHDRAW',
            ])->comment('流水类型');

            $table->enum('type', [
                'CREAT',
                'SHARE',
                'WITHDRAW',
                'WITHDRAW_REJECT',
            ])->comment('流水来源类型：创作，分享');
            
            $table->enum('status', [
                'DEFAULT',
                'AUDIT_PENDING',
                'REJECTED',
                'PENDING',
                'DONE',
            ])->default('DEFAULT')->comment('状态:默认[未提现],待审核,审核被拒,处理中[等待回调],完成');
            
            $table->decimal('amount', 10, 2)->comment('金额变动');
            $table->string('note', 64)->default('')->comment('备注');
            $table->json('payload')->comment('payload');
            $table->json('additions')->comment('附加信息');

            $table->timestamps();
            
            $table->index(['user_id', 'action', 'status']);
            $table->index('order_id');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_user_transaction');
    }
}
