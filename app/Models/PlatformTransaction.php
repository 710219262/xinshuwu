<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 27/04/2019
 * Time: 15:02
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PlatformTransaction
 * Simple order status state machine
 * ====================================================================================================================
 *
 *                       ====>
 *                  WECHAT|ALIPAY PAYED [ACTION IN]
 * 1    User(customer) - - - - - - - - - - - - - PLATFORM
 *      |
 *      |               ====>                                ====>
 *      |       confirm receive goods                WECHAT|ALIPAY TRANSFER [ACTION OUT]
 *      |  - - - - - - - - - - - - -   PLATFORM    - - - - - - - - - - - - - MERCHANT
 *
 *
 *
 *                          ====>
 *                      WITHDRAW SHARE REQUEST
 * 2    User(aff man) - - - - - - - - - - - - - - - - - - PLATFORM
 *      |                                                      |
 *      |              WECHAT|ALIPAY TRANSFER [ACTION OUT]     |
 *      |                   <====                              |
 *      |               - - - - - - - - - - - - - - - - - - - -|
 *
 *
 *                          ====>
 *                      WITHDRAW SHARE REQUEST
 * 3    MERCHANT(aff man) - - - - - - - - - - - - - - - - - - PLATFORM
 *      |                                                      |
 *      |              WECHAT|ALIPAY TRANSFER [ACTION OUT]     |
 *      |                   <====                              |
 *      |               - - - - - - - - - - - - - - - - - - - -|
 *
 *                           ====>
 *                       WITHDRAW REQUEST
 *  4   PLATFORM  - - - - - - - - - - - - - - - - - - - WECHAT|ALIPAY
 *                                                                   |
 *                     WECHAT|ALIPAY TRANSFER [WITHDRAW]             |
 *                           <====                                   |
 *      PLATFORM(BANKCARD)  - - - - - - - - - - - - - - - - - - - -  |
 *
 *
 * ====================================================================================================================
 * @package App\Models
 * @property integer $id
 * @property double $amount
 * @property string $action
 * @property string $target
 * @property integer $target_id
 * @property integer $refer_id
 * @property string $note
 * @property string $additions
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PlatformTransaction extends Model
{
    protected $table = 'xsw_platform_transaction';

    protected $guarded = ['id'];

    protected $casts = [
        'additions' => 'array',
    ];

    const A_IN = 'IN';
    const A_OUT = 'OUT';
    const A_WITHDRAW = 'WITHDRAW';

    const T_USER = 'USER';
    const T_MERCHANT = 'MERCHANT';
    const T_PLATFORM = 'PLATFORM';

    const TYPE_PAY_GOODS = 'PAY_GOODS';
    const TYPE_PAY_VIP = 'PAY_VIP';
    const TYPE_CREAT_EXP = 'CREAT_EXP';
    const TYPE_SHARE = 'SHARE';
    const TYPE_CREAT_ARTICLE = 'CREAT_ARTICLE';
    const TYPE_GOODS_SELL = 'GOODS_SELL';
    const TYPE_REFUND = 'REFUND';


    const N_USER_PAY = '用户购物支付';
    const N_USER_PAY_VIP = '用户VIP会员购买支付';
    const N_USER_CREAT = '用户创作分成';
    const N_USER_SHARE = '用户分享分成';
    const N_PLATFORM_CREAT = '平台创作分成';
    const N_MERCHANT_SELL = '商户销售额分流';
    const N_REFUND = '售后退款';

    /**
     * 收入
     * @param $payInfo
     * @param array $additions
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public static function payIn($payInfo, Array $additions = [])
    {
        $transaction = PlatformTransaction::query()
            ->create([
                'action'    => self::A_IN,
                'type'      => $payInfo['type'],
                'target'    => $payInfo['target'],
                'target_id' => $payInfo['target_id'],
                'refer_id'  => $payInfo['refer_id'],
                'amount'    => $payInfo['amount'],
                'note'      => $payInfo['note'],
                'additions' => $additions
            ]);
        return $transaction;
    }

    /**
     * 支出
     * @param $payInfo
     * @param array $additions
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public static function payOut($payInfo, Array $additions = [])
    {
        $transaction = PlatformTransaction::query()
            ->create([
                'action'    => self::A_OUT,
                'type'      => $payInfo['type'],
                'target'    => $payInfo['target'],
                'target_id' => $payInfo['target_id'],
                'refer_id'  => $payInfo['refer_id'],
                'amount'    => my_sub(0, $payInfo['amount']),
                'note'      => $payInfo['note'],
                'additions' => $additions
            ]);
        return $transaction;
    }
}
