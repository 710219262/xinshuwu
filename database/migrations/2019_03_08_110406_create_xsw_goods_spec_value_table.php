<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswGoodsSpecValueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_goods_spec_value', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('spec_id')->unsigned()->comment('规格id');
            $table->string('value', 64)->default('')->comment('值');
            $table->timestamps();
            
            $table->index(['spec_id', 'value']);
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_goods_spec_value');
    }
}
