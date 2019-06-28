<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserFollowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_follow', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('follower_id')->unsigned()->comment('关注者id');
            $table->integer('followed_id')->unsigned()->comment('被关注用户id');
            
            $table->index('follower_id');
            $table->index('followed_id');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_user_follow');
    }
}
