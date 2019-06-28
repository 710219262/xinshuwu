<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswGoodsSpecGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_goods_spec_group', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sku_id')->unsigned()->comment('组id');
            $table->integer('sv_id')->unsigned()->comment('规格值id');
            $table->string('sv')->default('')->comment('规格值');
            $table->timestamps();
            
            $table->index('sku_id');
            $table->index('sv_id');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_goods_spec_group');
    }
}
