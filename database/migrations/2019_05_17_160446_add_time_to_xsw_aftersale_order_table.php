<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeToXswAftersaleOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_aftersale_order', function (Blueprint $table) {
            $table->timestamp('audit_at')->nullable()->comment('商家审核时间');
            $table->timestamp('receive_at')->nullable()->comment('商家确认收货时间');
            $table->timestamp('refund_at')->nullable()->comment('退款成功时间');
            $table->timestamp('cancel_at')->nullable()->comment('用户取消退款申请时间');
            $table->timestamp('dispatch_at')->nullable()->comment('用户发货时间');
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
            $table->dropColumn('audit_at');
            $table->dropColumn('dispatch_at');
            $table->dropColumn('receive_at');
            $table->dropColumn('refund_at');
            $table->dropColumn('cancel_at');
        });
    }
}
