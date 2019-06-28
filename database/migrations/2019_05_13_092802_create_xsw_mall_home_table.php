<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswMallHomeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_mall_home', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('pid')->unsigned()->default(0)->comment('父级id');
            $table->tinyInteger('level')->unsigned()->default(0)->comment('层级');
            $table->string('cover', 64)->default('')->comment('封面url');
            $table->string('title')->default('')->comment('标题');
            $table->text('content')->comment('内容');
            $table->timestamps();
            $table->softDeletes();
            $table->index('pid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_mall_home');
    }
}
