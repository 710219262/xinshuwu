<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserCollectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_collection', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('user_id')->unsigned()->comment('用户ID');
            $table->integer('collect_id')->unsigned()->comment('收藏ID');
            $table->enum('type', [
                'ARTICLE',
                'EXP',
                'GOODS',
                'STORE',
            ])->default('ARTICLE')->comment('类型');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('collect_id');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_user_collection');
    }
}
