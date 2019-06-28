<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusRejectReasonColumnOnUserExpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_user_exp', function (Blueprint $table) {
            $table->enum('status', [
                'CREATED',
                'REJECTED',
                'COMPLETED'
            ])->default('CREATED')->comment('状态');
            $table->string('reject_reason')->default('')->comment('审核被拒原因');
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
            $table->dropColumn('status');
            $table->dropColumn('reject_reason');
        });
    }
}
