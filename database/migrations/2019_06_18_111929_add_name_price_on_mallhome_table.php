<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNamePriceOnMallhomeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_mall_home', function (Blueprint $table) {
            $table->string('name', 32)->default('')->comment('名称');
            $table->decimal('price', 8, 2)->unsigned()->comment('价格');
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
            $table->dropColumn('name');
            $table->dropColumn('price');
        });
    }
}
