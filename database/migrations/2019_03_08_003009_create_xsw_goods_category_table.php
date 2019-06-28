<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswGoodsCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_goods_category', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('pid')->unsigned()->default(0)->comment('父级id');
            $table->string('name', 128)->default('')->comment('分类');
            $table->tinyInteger('level')->unsigned()->default(0)->comment('层级');
            $table->enum('group', ['MERCHANT', 'APP'])->default('MERCHANT')
                ->comment('商户端APP端(主要是1级分类)');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('pid');
            $table->index('group');
            
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_goods_category');
    }
}
