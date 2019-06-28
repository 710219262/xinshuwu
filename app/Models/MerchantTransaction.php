<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 27/04/2019
 * Time: 16:09
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MerchantTransaction
 *
 * @package App\Models
 * @property integer $id
 * @property integer $store_id
 * @property integer $refer_id
 * @property double  $amount
 * @property string  $action
 * @property string  $type
 * @property string  $note
 * @property string  $status
 * @property array   $additions
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class MerchantTransaction extends Model
{
    protected $table = 'xsw_merchant_transaction';
    
    protected $guarded = ['id'];

    protected $casts = [
        'additions' => 'array',
    ];
    
    const A_INCOME = 'INCOME';
    const A_WITHDRAW = 'WITHDRAW';
    
    const T_SHARE = 'SHARE';
    const T_SALE = 'SALE';
    const T_WITHDRAW = 'WITHDRAW';
    const T_WITHDRAW_REJECT = 'WITHDRAW_REJECT';

    const S_DEFAULT = 'DEFAULT'; // 默认状态
    const S_AUDIT_PENDING = 'AUDIT_PENDING'; // 等待审核
    const S_REJECTED = 'REJECTED'; // 系统处理拒绝申请完成
    const S_PENDING = 'PENDING'; // 管理员通过提现申请，等待系统处理
    const S_PROCESSING = 'PROCESSING'; // 提现中
    const S_DONE = 'DONE'; // 提现完成

    const N_GOODS_SELL = '商品售出收入';
    const N_WITHDRAW = '提现';
    const N_WITHDRAW_REJECT = '提现驳回';
    const N_WITHDRAW_REJECT_REFUND = '提现驳回退款';

    const M_NONE = 'NONE';
    const M_ALIPAY = 'ALIPAY';
    const M_WECHAT = 'WECHAT';
    const M_PERSONAL_BANKCARD = 'PERSONAL_BANKCARD';
    const M_COMPANY_BANKCARD = 'COMPANY_BANKCARD';

    /**
     * 收入
     * @param $payInfo
     * @param array $additions
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public static function payIn($payInfo, Array $additions = [])
    {
        $transaction = MerchantTransaction::query()
            ->create([
                'action'    => self::A_INCOME,
                'type'      => $payInfo['type'],
                'store_id'  => $payInfo['store_id'],
                'refer_id'  => $payInfo['refer_id'],
                'amount'    => $payInfo['amount'],
                'note'      => $payInfo['note'],
                'additions' => $additions
            ]);
        return $transaction;
    }

    /**
     * 提现申请
     * @param $payInfo
     * @param array $accountInfo
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public static function withdrawApply($payInfo, Array $accountInfo)
    {
        $storeId = $payInfo['store_id'];
        $accountInfo['withdraw_no'] = MerchantTransaction::newWithdrawNum($storeId);

        $transaction = MerchantTransaction::query()
            ->create([
                'action'    => self::A_WITHDRAW,
                'type'      => self::T_WITHDRAW,
                'status'    => self::S_AUDIT_PENDING,
                'note'      => self::N_WITHDRAW,
                'store_id'  => $storeId,
                'amount'    => my_sub(0, $payInfo['amount']),
                'additions' => $accountInfo
            ]);

        return $transaction;
    }

    public static function withdrawFinished($id)
    {
        MerchantTransaction::query()
            ->where('additions->withdraw_no', $id)
            ->update([
                'status' => MerchantTransaction::S_DONE
            ]);
    }

    /**
     * @param $id
     * @return string
     */
    public static function newWithdrawNum($id)
    {
        $withdrawNo = self::generateNum($id);
        while (MerchantTransaction::query()
                ->where('additions->withdraw_no', $withdrawNo)->count() > 0) {
            $withdrawNo = MerchantTransaction::generateNum($id);
        }
        return $withdrawNo;
    }

    /**
     * @param $id
     * @return string
     */
    public static function generateNum($id)
    {
        return sprintf(
            "%s%08d",
            time(),
            $id
        );
    }
}
