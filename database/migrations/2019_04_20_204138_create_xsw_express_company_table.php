<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswExpressCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_express_company', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('name')->default('')->comment('快递公司名称');
            $table->string('abbr')->default('')->comment('快递公司缩写');
            
            $table->timestamps();
            
            $table->index('name');
            $table->index('abbr');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_express_company');
    }
}
