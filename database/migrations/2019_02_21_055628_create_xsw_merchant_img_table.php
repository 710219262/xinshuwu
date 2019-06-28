<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswMerchantImgTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_merchant_img', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('merchant_id')->unsigned()->comment('商家id');
            
            $table->enum('type', [
                'CERT',
                'PRODUCT',
            ])->default('CERT')->comment('类别');
            
            $table->string('url')->default('')->comment('图片地址');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['merchant_id', 'type']);
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_merchant_img');
    }
}
