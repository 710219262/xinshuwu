<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswArticleLikeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_article_like', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('article_id')->unsigned()->comment('文章ID');
            $table->integer('user_id')->unsigned()->comment('评论用户ID');
            
            $table->timestamps();
            $table->index(['article_id', 'user_id']);
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_article_like');
    }
}
