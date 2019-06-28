<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAffToXswUserShoppingCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_user_shopping_cart', function (Blueprint $table) {
            $table->string('aff')->default('')->comment('推广码');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xsw_user_shopping_cart', function (Blueprint $table) {
            $table->dropColumn('aff');
        });
    }
}
