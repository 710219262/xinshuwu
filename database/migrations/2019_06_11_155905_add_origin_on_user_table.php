<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOriginOnUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_user', function (Blueprint $table) {
            $table->enum('origin', [
                'android',
                'ios',
                'web',
                'wap',
                'wechat',
                'other'
            ])->default('other')->comment('注册会员来源');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xsw_user', function (Blueprint $table) {
            $table->dropColumn('origin');
        });
    }
}
