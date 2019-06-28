<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/13
 * Time: 17:09
 */

namespace App\Listeners;

use App\Events\Transaction\UserWithdrawAudited;
use App\Models\UserTransaction;
use Yansongda\Pay\Gateways\Alipay;

class UserWithdrawAuditedListener
{
    /**
     * @var UserTransaction $transaction
     */
    protected $transaction;

    /**
     * @param UserWithdrawAudited $event
     * @throws \Exception
     */
    public function handle(UserWithdrawAudited $event)
    {
        try {
            \DB::beginTransaction();

            $this->transaction = UserTransaction::query()
                ->where('id', $event->transaction->id)
                ->where('type', UserTransaction::T_WITHDRAW)
                ->whereIn('status', [UserTransaction::S_PENDING, UserTransaction::S_REJECTED])
                ->lockForUpdate()
                ->first();

            if ($this->transaction) {
                switch ($this->transaction->status) {
                    case UserTransaction::S_REJECTED:
                        $this->reject();
                        break;
                    case UserTransaction::S_PENDING:
                        $this->pass();
                        break;
                    default:
                        break;
                }
            }

            \DB::commit();
        } catch (\Exception $e) {
            \Log::error('用户提现审核结算异常', $e->getTrace());
            \DB::rollBack();
        }
    }

    protected function reject()
    {
        UserTransaction::payIn([
            'type'     => UserTransaction::T_WITHDRAW_REJECT,
            'note'     => UserTransaction::N_WITHDRAW_REJECT_REFUND,
            'user_id'  => $this->transaction->user_id,
            'amount'   => my_sub(0, $this->transaction->amount),
        ]);
    }

    protected function pass()
    {
        $accountInfo = $this->transaction->additions;

        switch ($accountInfo['account_type']) {
            case UserTransaction::M_ALIPAY:
                $this->withdrawToAliAccount();
                break;
            default:
                break;
        }
    }

    /**
     * 提现到支付宝
     */
    protected function withdrawToAliAccount()
    {
        $accountInfo = $this->transaction->additions;

        /** @var Alipay $alipay */
        $alipay = app('pay.alipay');
        $res = $alipay->transfer([
            'notify_url'      => config('xsw.withdraw.ali_notify_url'),
            'payee_type'      => 'ALIPAY_LOGONID',
            'out_biz_no'      => $accountInfo['withdraw_no'],
            'payee_account'   => $accountInfo['account'],
            'payee_real_name' => $accountInfo['name'],
            'amount'          => my_sub(0, $this->transaction->amount),
            'attach'          => json_encode(['type' => 'USER']),
            'remark'          => '猩事物商户提现'
        ]);
        \Log::info("transfer_merchant_ali_notify", $res->toArray());
        UserTransaction::query()
            ->where('id', $this->transaction->id)
            ->update([
                'status' => UserTransaction::S_PROCESSING
            ]);
    }
}
