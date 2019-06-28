<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswMerchantTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_merchant_transaction', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('store_id')->unsigned()->comment('商户ID');
            $table->decimal('amount', 10, 2)->comment('金额');
            
            $table->enum('action', [
                'INCOME',
                'WITHDRAW',
            ])->comment('类型:收入,取款');
            
            $table->integer('refer_id')->default(0)->unsigned()->comment('关联表ID 订单|文章');
            
            $table->enum('type', [
                'SHARE',
                'SALE',
                'WITHDRAW',
                'WITHDRAW_REJECT',
            ])->comment('流水来源类型');
            
            $table->enum('status', [
                'DEFAULT',
                'AUDIT_PENDING',
                'REJECTING',
                'PROCESSING',
                'REJECTED',
                'PENDING',
                'DONE',
            ])->default('DEFAULT')->comment('状态:默认[未提现],待审核,审核被拒,处理中[等待回调],完成');
            
            $table->string('note')->default('')->comment('备注');
            
            $table->timestamps();
            
            $table->index('refer_id');
            $table->index('store_id');
            $table->index('amount');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_merchant_transaction');
    }
}
