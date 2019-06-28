<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_user', function (Blueprint $table) {
            $table->increments('id');
            
            
            $table->char('phone', 11)->default('')->comment('手机号');
            $table->string('qq_uid')->default('')->comment('QQ登录uid');
            $table->string('wb_uid')->default('')->comment('微博登录uid');
            $table->string('wechat_uid')->default('')->comment('微信登录uid');
            
            $table->string('avatar', 256)->default('')->comment('头像');
            $table->string('nickname', 32)->default('')->comment('昵称');
            $table->string('motto')->default('')->comment('个人描述');
            $table->enum('gender', ['MALE', 'FEMALE'])->default('MALE')->comment('性别');
            $table->date('birthday')->comment('用户生日');
            
            
            $table->integer('fans_count')->unsigned()->default(0)->comment('粉丝数量');
            $table->integer('follow_count')->unsigned()->default(0)->comment('关注数量');
            $table->integer('favorite_count')->unsigned()->default(0)->comment('收藏数量');
            $table->integer('liked_count')->unsigned()->default(0)->comment('被赞数量');
            $table->integer('xb')->unsigned()->default(0)->comment('猩币数量');
            
            $table->tinyInteger('vip')->unsigned()->default(0)->comment('VIP状态');
            $table->timestamp('vip_card')->nullable()->comment('vip有效期');
            
            $table->integer('region_id')->unsigned()->comment('地区id');
            $table->string('region')->comment('地区');
            
            
            $table->tinyInteger('status')->unsigned()->default(0)->comment('账号状态');
            
            $table->timestamps();
            
            $table->index('phone');
            $table->index('qq_uid');
            $table->index('wb_uid');
            $table->index('wechat_uid');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_user');
    }
}
