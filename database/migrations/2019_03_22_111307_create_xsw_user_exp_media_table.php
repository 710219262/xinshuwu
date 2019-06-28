<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserExpMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_exp_media', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('user_id')->unsigned()->comment('发布体验的用户ID');
            $table->integer('exp_id')->unsigned()->comment('体验ID');
            $table->string('url')->comment('URL');
            $table->enum('type', ['IMG', 'VIDEO'])->default('IMG')->comment('媒体类型');
            $table->mediumInteger('height')->unsigned()->comment('宽度');
            $table->mediumInteger('width')->unsigned()->comment('高度');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('exp_id');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_user_exp_media');
    }
}
