<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/10
 * Time: 15:22
 */

namespace App\Listeners;

use App\Events\Transaction\MerchantWithdrawAudited;
use App\Models\MerchantTransaction;
use Yansongda\Pay\Gateways\Alipay;

class MerchantWithdrawAuditedListener
{
    /**
     * @var MerchantTransaction $transaction
     */
    protected $transaction;

    /**
     * @param MerchantWithdrawAudited $event
     * @throws \Exception
     */
    public function handle(MerchantWithdrawAudited $event)
    {
        try {
            \DB::beginTransaction();

            $this->transaction = MerchantTransaction::query()
                ->where('id', $event->transaction->id)
                ->where('type', MerchantTransaction::T_WITHDRAW)
                ->whereIn('status', [MerchantTransaction::S_PENDING, MerchantTransaction::S_REJECTED])
                ->lockForUpdate()
                ->first();

            if ($this->transaction) {
                switch ($this->transaction->status) {
                    case MerchantTransaction::S_REJECTED:
                        $this->reject();
                        break;
                    case MerchantTransaction::S_PENDING:
                        $this->pass();
                        break;
                    default:
                        break;
                }
            }

            \DB::commit();
        } catch (\Exception $e) {
            \Log::error('商户提现审核结算异常', $e->getTrace());
            \DB::rollBack();
        }
    }

    protected function reject()
    {
        MerchantTransaction::payIn([
            'type'     => MerchantTransaction::T_WITHDRAW_REJECT,
            'store_id' => $this->transaction->store_id,
            'refer_id' => $this->transaction->id,
            'amount'   => my_sub(0, $this->transaction->amount),
            'note'     => MerchantTransaction::N_WITHDRAW_REJECT_REFUND
        ]);
    }

    protected function pass()
    {
        $accountInfo = $this->transaction->additions;

        switch ($accountInfo['account_type']) {
            case MerchantTransaction::M_ALIPAY:
                $this->withdrawToAliAccount();
                break;
            case MerchantTransaction::M_PERSONAL_BANKCARD:
            case MerchantTransaction::M_COMPANY_BANKCARD:
                $this->withdrawToBankCard();
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
        $alipay->transfer([
            'notify_url'      => config('xsw.withdraw.ali_notify_url'),
            'payee_type'      => 'ALIPAY_LOGONID',
            'out_biz_no'      => $accountInfo['withdraw_no'],
            'payee_account'   => $accountInfo['account'],
            'payee_real_name' => $accountInfo['name'],
            'amount'          => my_sub(0, $this->transaction->amount),
            'passback_params' => ['type' => 'MERCHANT'],
            'remark'          => '猩事物商户提现'
        ]);

        MerchantTransaction::query()
            ->where('id', $this->transaction->id)
            ->update([
                'status' => MerchantTransaction::S_PROCESSING
            ]);
    }

    /**
     * 提现到银行卡
     */
    protected function withdrawToBankCard()
    {
        MerchantTransaction::withdrawFinished($this->transaction->id);
    }
}
