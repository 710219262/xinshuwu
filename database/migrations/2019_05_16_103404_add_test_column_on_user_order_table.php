<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTestColumnOnUserOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_user_order', function (Blueprint $table) {
            $table->integer('is_test')->unsigned()->default(1)->after('is_deleted')->comment('内测标志');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xsw_user_order', function (Blueprint $table) {
            $table->dropColumn('is_test');
        });
    }
}
