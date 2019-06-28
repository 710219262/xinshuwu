<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderNoToXswAftersaleOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_aftersale_order', function (Blueprint $table) {
            $table->string('order_no', 32)->default('')->comment('原订单号');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xsw_aftersale_order', function (Blueprint $table) {
            $table->dropColumn('order_no');
        });
    }
}
