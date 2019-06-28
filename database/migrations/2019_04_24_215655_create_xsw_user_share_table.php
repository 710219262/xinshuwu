<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserShareTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_share', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('user_id')->unsigned()->comment('用户ID');
            $table->integer('goods_id')->unsigned()->comment('商品ID');
            $table->integer('store_id')->unsigned()->comment('店铺ID');
            $table->integer('target_id')->unsigned()->comment('目标ID');
            
            $table->enum('target', [
                'EXP',
                'ARTICLE',
                'GOODS',
            ])->default('EXP')->comment('目标');
            
            $table->string('aff', 64)->comment('');
            
            $table->integer('view')->default(0)->unsigned()->comment('浏览量');
            
            $table->decimal('income', 10, 2)->default(0)->comment('累计收益');
            
            $table->index('user_id');
            $table->index('goods_id');
            $table->index('store_id');
            $table->index(['target', 'target_id']);
            $table->unique('aff');
            
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
        Schema::dropIfExists('xsw_user_share');
    }
}
