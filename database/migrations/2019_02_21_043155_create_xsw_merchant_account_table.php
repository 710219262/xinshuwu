<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswMerchantAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_merchant_account', function (Blueprint $table) {
            $table->increments('id');
            $table->char('phone', 11)->default('')->comment('手机号');
            $table->string('name')->default('')->comment('店铺名称');
            $table->string('category_id')->default(0)->comment('主营类目');
            $table->string('logo', 256)->default('')->comment('店铺logo');
            $table->string('desc')->default('')->comment('店铺详情');
            $table->tinyInteger('status')->default(-1)->comment('店铺状态');
            
            $table->unique('phone');
            
            $table->timestamps();
        });
        
        DB::update("ALTER TABLE xsw_merchant_account AUTO_INCREMENT = 600000;");
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_merchant_account');
    }
}
