<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswPlatformTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_platform_transaction', function (Blueprint $table) {
            $table->increments('id');
            
            $table->decimal('amount', 10, 2)->default(0)->comment('金额变动');
            
            $table->enum('action', [
                'IN',
                'OUT',
                'WITHDRAW',
            ])->comment('类型:流进[支付],流出[打款],取出[取款]');
            
            $table->enum('target', [
                'USER',
                'MERCHANT',
                'PLATFORM',
            ]);
            
            $table->integer('target_id')->default(0)
                ->unsigned()->comment('目标id:[商户ID,用户ID]');
            
            $table->integer('refer_id')->default(0)
                ->unsigned()->comment('关联数据表ID');
            
            $table->string('note')->default('')->comment('备注');
            
            $table->timestamps();
            
            $table->index(['action', 'target', 'amount']);
            $table->index(['action', 'amount']);
            $table->index(['action', 'target_id']);
            
            $table->index('amount');
            $table->index('target_id');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_platform_transaction');
    }
}
