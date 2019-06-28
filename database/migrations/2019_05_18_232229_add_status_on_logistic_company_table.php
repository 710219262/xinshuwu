<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusOnLogisticCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_express_company', function (Blueprint $table) {
            $table->tinyInteger('status')->unsigned()
                ->after('abbr')
                ->default(0)
                ->comment('状态');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xsw_express_company', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
