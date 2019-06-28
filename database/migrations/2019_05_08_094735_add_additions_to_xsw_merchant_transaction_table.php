<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionsToXswMerchantTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_merchant_transaction', function (Blueprint $table) {
            $table->json('additions')->default('')->comment('附加信息');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xsw_merchant_transaction', function (Blueprint $table) {
            $table->dropColumn('additions');
        });
    }
}
