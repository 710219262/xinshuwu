<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncomeToXswUserExpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_user_exp', function (Blueprint $table) {
            $table->decimal('income', 10, 2)->default(0)->comment('累计收益');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xsw_user_exp', function (Blueprint $table) {
            $table->dropColumn('income');
        });
    }
}
