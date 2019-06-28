<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 27/04/2019
 * Time: 09:58
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Class UserTransaction
 *
 * @package App\Models
 * @property integer $id
 * @property integer $user_id
 * @property integer $order_no
 * @property integer $target_id
 * @property string  $action
 * @property string  $type
 * @property string  $status
 * @property double  $amount
 * @property string  $note
 * @property array   $additions
 * @property array   $payload
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class UserTransaction extends Model
{
    protected $table = 'xsw_user_transaction';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'amount'    => 'double',
        'additions' => 'array',
        'payload'   => 'array',
        'balance'   => 'double',
    ];
    
    protected $appends = ['status_str'];
    
    const A_INCOME = 'INCOME';
    const A_WITHDRAW = 'WITHDRAW';
    
    const T_CREAT = 'CREAT';
    const T_SHARE = 'SHARE';
    const T_WITHDRAW = 'WITHDRAW';
    const T_WITHDRAW_REJECT = 'WITHDRAW_REJECT';
    
    const S_DEFAULT = 'DEFAULT'; // 默认状态
    const S_AUDIT_PENDING = 'AUDIT_PENDING'; // 等待审核
    const S_REJECTING = 'REJECTING'; // 管理员拒绝提现申请，等待系统处理
    const S_REJECTED = 'REJECTED'; // 系统处理拒绝申请完成
    const S_PENDING = 'PENDING'; // 管理员通过提现申请，等待系统处理
    const S_PROCESSING = 'PROCESSING'; // 提现中
    const S_DONE = 'DONE'; // 提现完成
    
    const N_USER_WITHDRAW = '用户申请提现';
    const N_USER_CREAT = '创作分成';
    const N_USER_SHARE = '分享分成';
    const N_WITHDRAW_REJECT = '提现驳回';
    const N_WITHDRAW_REJECT_REFUND = '提现驳回退款';
    const N_WITHDRAW = '提现';

    const T_MAPPING = [
        self::S_AUDIT_PENDING => '等待审核',
        self::S_REJECTED      => '申请提现被拒绝',
        self::S_PENDING       => '待打款',
        self::S_DONE          => '提现成功',
    ];
    
    const M_NONE = 'NONE';
    const M_ALIPAY = 'ALIPAY';
    const M_WECHAT = 'WECHAT';
    const M_BANKCARD = 'BANKCARD';
    
    public function getStatusStrAttribute()
    {
        return array_get(self::T_MAPPING, $this->status, '未知状态');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 收入
     *
     * @param       $payInfo
     * @param array $additions
     *
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public static function payIn($payInfo, Array $additions = [])
    {
        $transaction = UserTransaction::query()
            ->create([
                'action'    => self::A_INCOME,
                'user_id'   => $payInfo['user_id'],
                'share_id'  => array_get($payInfo, 'share_id', 0),
                'order_id'  => array_get($payInfo, 'order_id', 0),
                'type'      => $payInfo['type'],
                'amount'    => $payInfo['amount'],
                'note'      => $payInfo['note'],
                'additions' => $additions,
            ]);
        return $transaction;
    }
    
    /**
     * 提现申请
     *
     * @param       $payInfo
     * @param array $accountInfo
     *
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public static function withdrawApply($payInfo, Array $accountInfo)
    {
        $userId = $payInfo['user_id'];
        $accountInfo['withdraw_no'] = UserTransaction::newWithdrawNum($userId);
        
        $transaction = UserTransaction::query()
            ->create([
                'action'    => self::A_WITHDRAW,
                'type'      => self::T_WITHDRAW,
                'status'    => self::S_AUDIT_PENDING,
                'note'      => self::N_USER_WITHDRAW,
                'user_id'   => $userId,
                'amount'    => my_sub(0, $payInfo['amount']),
                'additions' => $accountInfo,
                'payload'   => self::buildPayload(),
            ]);
        
        return $transaction;
    }

    public static function withdrawFinished($id)
    {
        UserTransaction::query()
            ->where('additions->withdraw_no', $id)
            ->update([
                'status' => UserTransaction::S_DONE
            ]);
    }
    
    public static function buildPayload()
    {
        $request = Request::capture();
        
        return [
            'body'    => $request->all(),
            'url'     => $request->url(),
            'ip'      => $request->getClientIp(),
            'headers' => $request->headers->all(),
        ];
    }
    
    /**
     * @param $id
     *
     * @return string
     */
    public static function newWithdrawNum($id)
    {
        $withdrawNo = self::generateNum($id);
        while (UserTransaction::query()
                ->where('additions->withdraw_no', $withdrawNo)->count() > 0) {
            $withdrawNo = UserTransaction::generateNum($id);
        }
        return $withdrawNo;
    }
    
    /**
     * @param $id
     *
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
