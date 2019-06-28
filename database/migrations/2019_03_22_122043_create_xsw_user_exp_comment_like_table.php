<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserExpCommentLikeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_exp_comment_like', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('comment_id')->unsigned()->comment('评论ID');
            $table->integer('user_id')->unsigned()->comment('评论用户ID');
            
            $table->timestamps();
            $table->index(['comment_id', 'user_id']);
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_user_exp_comment_like');
    }
}
