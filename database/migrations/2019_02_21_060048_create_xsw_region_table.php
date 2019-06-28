<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswRegionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_region', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('region_id')->unsigned()->comment('地区id');
            $table->integer('parent_id')->default(0)->unsigned()->comment('父级id');
            $table->string('name', 64)->default('')->comment('名称');
            $table->string('region_name', 256)->default('')->comment('全名称');
            $table->string('name_en', 64)->default('')->comment('英文名称');
            $table->tinyInteger('is_foreign')->unsigned()->default(0)->comment('是否是外国');
            $table->tinyInteger('level')->unsigned()->default(1)->comment('层级');
            
            $table->timestamps();
            
            $table->index('region_id');
            $table->index('parent_id');
            $table->index('is_foreign');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_region');
    }
}
