<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_feedback', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('user_id')->unsigned()->comment('用户ID');
            $table->text('content')->default('')->comment('内容');
            $table->string('mobile', 16)->default('')->comment('手机号');
            
            
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_feedback');
    }
}
