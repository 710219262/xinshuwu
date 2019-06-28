<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_admin', function (Blueprint $table) {
            $table->increments('id');
            $table->char('phone', 11)->default('')->comment('手机号');
            $table->string('name')->default('')->comment('管理员名称');
            $table->tinyInteger('status')->default(0)->comment('管理员状态');
            
            $table->unique('phone');
            
            $table->timestamps();
        });
        
        DB::update("ALTER TABLE xsw_admin AUTO_INCREMENT = 100000;");
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_admin');
    }
}
