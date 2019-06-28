<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTypeToXswPlatformTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $str = <<<EOD
        ALTER TABLE 
          `xsw_platform_transaction`  
        MODIFY COLUMN
              `type` enum(
              'PAY_GOODS',
              'PAY_VIP',
              'CREAT_EXP',
              'CREAT_ARTICLE',
              'SHARE',
              'GOODS_SELL',
              'REFUND') NOT NULL DEFAULT 'PAY_GOODS' COMMENT '流水来源类型：商品支付，VIP会员支付，体验分成，文章分成，分享分成，商品成交，售后退款';
EOD;
        \DB::statement($str);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $str = <<<EOD
        ALTER TABLE 
          `xsw_platform_transaction`  
        MODIFY COLUMN
              `type` enum(
              'PAY_GOODS',
              'PAY_VIP',
              'CREAT_EXP',
              'CREAT_ARTICLE',
              'SHARE',
              'GOODS_SELL') NOT NULL DEFAULT 'PAY_GOODS' COMMENT '流水来源类型：商品支付，VIP会员支付，体验分成，文章分成，分享分成，商品成交';
EOD;
        \DB::statement($str);
    }
}
