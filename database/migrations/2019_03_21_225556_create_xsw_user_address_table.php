<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user_address', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('user_id')->unsigned()->comment('用户ID');
            $table->string('contact')->comment('联系人');
            $table->string('phone', 16)->comment('电话');
            
            $table->integer('province_id')->unsigned()->comment('省ID');
            $table->integer('city_id')->unsigned()->comment('市ID');
            $table->integer('area_id')->unsigned()->comment('区ID');
            
            $table->string('region')->default('')->comment('冗余省市区');
            
            $table->string('address')->comment('地址');
            $table->string('number')->comment('门牌号');
            $table->enum('tag', ['HOME', 'COMPANY', 'OTHER'])
                ->default('HOME')->comment('标签');
            
            $table->tinyInteger('is_default')->default(0)->comment('是否是默认收货地址');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_user_address');
    }
}
