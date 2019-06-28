<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_video', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('author_id')->unsigned()->default(0)->comment('用户id');
            $table->string('cover', 64)->default('')->comment('封面url');
            $table->string('title')->default('')->comment('标题');
            $table->text('content')->comment('内容');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['author_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_video');
    }
}
