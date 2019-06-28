<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToXswUserVipOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_user_vip_order', function (Blueprint $table) {
            $table->enum('type', [
                'MONTH',
                'SEASON',
                'YEAR'
            ])->default('MONTH')->comment('会员卡类型');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xsw_user_vip_order', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
