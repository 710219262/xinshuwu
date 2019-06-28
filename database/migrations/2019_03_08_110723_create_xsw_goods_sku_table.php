<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswGoodsSkuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_goods_sku', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('goods_id')->unsigned()->comment('商品id');
            $table->tinyInteger('has_spec')->unsigned()->default(0)->comment('是否有规格,默认0无规格');
            $table->integer('inventory')->unsigned()->default(0)->comment('库存');
            $table->decimal('price', 8, 2)->unsigned()->comment('价格');
            $table->decimal('market_price', 8, 2)->unsigned()->comment('市场价');
            $table->string('sku_name')->default('')->comment('sku名称');
            $table->string('sku_no')->default('')->comment('sku编码');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('goods_id');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_goods_sku');
    }
}
