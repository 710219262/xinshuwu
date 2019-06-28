<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionsAndTypeToXswPlatformTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_platform_transaction', function (Blueprint $table) {
            $table->json('additions')->default('')->comment('附加信息');
            $table->enum('type', [
                'PAY_GOODS',
                'PAY_VIP',
                'CREAT_EXP',
                'CREAT_ARTICLE',
                'SHARE',
                'GOODS_SELL'
            ])->default('PAY_GOODS')
                ->comment('流水来源类型：商品支付，VIP会员支付，体验分成，文章分成，分享分成，商品成交');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xsw_platform_transaction', function (Blueprint $table) {
            $table->dropColumn('additions');
            $table->dropColumn('type');
        });
    }
}
