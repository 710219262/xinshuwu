<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswGoodsInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_goods_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id')->unsigned()->comment('商户id merchant_account');
            $table->integer('category_id')->unsigned()->comment('分类id');
            
            $table->string('name')->default('')->comment('商品标题');
            $table->enum('type', ['COMMON', 'IMPORT'])->default('COMMON')->comment('商品类型');
            
            $table->enum('status', [
                'ON_SALE',
                'DRAFT',
                'SOLDOUT',
            ])->default('ON_SALE')->comment('状态');
            $table->json('sku_values')->comment('sku json冗余');
            $table->decimal('price', 10, 2)->unsigned()->default(0.1)->comment('冗余价格');
            $table->decimal('market_price', 10, 2)->unsigned()->default(0.1)->comment('冗余市场价格');
            $table->integer('inventory')->unsigned()->default(1)->comment('冗余库存');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'category_id']);
            $table->index(['category_id', 'status']);
            $table->index('status');
            $table->index('name');
        });
        
        DB::update("ALTER TABLE xsw_goods_info AUTO_INCREMENT = 100000;");
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_goods_info');
    }
}
