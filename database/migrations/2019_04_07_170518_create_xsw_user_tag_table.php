<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_tag', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('user_id')->unsigned()->comment('用户id');
            $table->integer('category_id')->unsigned()->comment('感兴趣的分类id');
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('category_id');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_user_tag');
    }
}
