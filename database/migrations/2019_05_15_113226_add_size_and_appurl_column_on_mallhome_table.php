<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSizeAndAppurlColumnOnMallhomeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_mall_home', function (Blueprint $table) {
            $table->string('size',30)->default('')
                ->after('content')->comment('图片尺寸');
            $table->string('appurl',30)->default('')
                ->after('size')->comment('app跳转id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xsw_mall_home', function (Blueprint $table) {
            $table->dropColumn('size');
            $table->dropColumn('appurl');
        });
    }
}
