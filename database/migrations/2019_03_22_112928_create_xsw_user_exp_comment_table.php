<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserExpCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_exp_comment', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('pid')->default(0)->unsigned()->comment('父级ID');
            $table->integer('exp_id')->unsigned()->comment('体验ID');
            $table->integer('user_id')->unsigned()->comment('评论用户ID');
            
            $table->integer('like')->default(0)->unsigned()->comment('点赞数量');
            $table->tinyInteger('is_author')->default(0)->unsigned()->comment('是否是作者');
            $table->string('content')->default('')->comment('评论内容');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('pid');
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
        Schema::dropIfExists('xsw_user_exp_comment');
    }
}
