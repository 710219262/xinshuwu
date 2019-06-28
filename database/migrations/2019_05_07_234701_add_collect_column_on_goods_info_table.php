<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCollectColumnOnGoodsInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_goods_info', function (Blueprint $table) {
            $table->integer('collect')->default(0)
                ->after('status')->unsigned()->comment('收藏数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xsw_goods_info', function (Blueprint $table) {
            $table->dropColumn('collect');
        });
    }
}
