<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswXSearchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_x_search', function (Blueprint $table) {
            $table->increments('id');
            
            $table->enum('type', [
                'ARTICLE',
                'COMMUNICATION',
                'NOVEL',
                'FRIEND',
                'SNACK',
            ]);
            
            $table->integer('count')->default(0)->unsigned();
            
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
        Schema::dropIfExists('xsw_x_search');
    }
}
