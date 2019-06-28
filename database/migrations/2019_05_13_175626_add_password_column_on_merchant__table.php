<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPasswordColumnOnMerchantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_merchant_account', function (Blueprint $table) {
            $table->string('password', 50)->default('')
                ->after('status')->comment('登录密码');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xsw_merchant_account', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
}
