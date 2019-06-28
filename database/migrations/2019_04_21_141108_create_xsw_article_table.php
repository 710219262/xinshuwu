<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswArticleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_article', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('category_id')->unsigned()->default(0)->comment('分类id');
            $table->integer('goods_id')->unsigned()->default(0)->comment('商品id');
            $table->integer('author_id')->unsigned()->default(0)->comment('用户id');
            
            $table->enum('publisher', [
                'MERCHANT',
                'USER',
                'PLATFORM',
            ])->default('MERCHANT')->comment('发布主体');
            
            $table->string('cover', 64)->default('')->comment('封面url');
            $table->string('title')->default('')->comment('标题');
            $table->text('content')->comment('内容');
            
            $table->enum('status', [
                'DRAFT',
                'AUDIT_PENDING',
                'REJECTED',
                'PUBLISHED',
                'OFFLINE',
            ])->default('DRAFT')->comment('状态');
            
            $table->timestamp('published_at');
            $table->integer('collect')->unsigned()->default(0)->comment('收藏数');
            $table->integer('like')->unsigned()->default(0)->comment('点赞数');
            $table->integer('share')->unsigned()->default(0)->comment('分享量');
            $table->integer('view')->unsigned()->default(0)->comment('浏览量');
            $table->integer('sale')->unsigned()->default(0)->comment('销量');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['author_id', 'publisher', 'status']);
            $table->index('title');
            $table->index('goods_id');
            $table->index('category_id');
            $table->index('publisher');
            $table->index('published_at');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_article');
    }
}
