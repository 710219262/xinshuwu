<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/9
 * Time: 16:22
 */

namespace App\Repos\Merchant;

use App\Events\Transaction\MerchantWithdrawAudited;
use App\Models\MerchantAccount;
use App\Models\MerchantTransaction;
use Carbon\Carbon;

class TransactionRepo
{
    /**
     * 账号详情
     * @param MerchantAccount $merchant
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function account(MerchantAccount $merchant)
    {
        $account = MerchantTransaction::query()
            ->where('store_id', $merchant->id)
            ->sharedLock()
            ->select(
                \DB::raw("IFNULL(SUM(amount),0) as balance"),
                \DB::raw("sum(if(action='WITHDRAW' and status<>'REJECTED', amount*(-1), 0)) as withdrawn"),
                \DB::raw("sum(if(action='INCOME' and status<>'WITHDRAW_REJECT', amount, 0)) as income")
            )
            ->first();

        return json_response([
            'balance'   => array_get($account, 'balance', 0),
            'withdrawn' => array_get($account, 'withdrawn', 0),
            'income'    => array_get($account, 'income', 0)
        ]);
    }

    /**
     * 今日账户详情
     * @param MerchantAccount $merchant
     * @return array
     */
    public function accountToday(MerchantAccount $merchant)
    {
        $account = MerchantTransaction::query()
            ->where('store_id', $merchant->id)
            ->where('created_at', '>', Carbon::today())
            ->sharedLock()
            ->select(
                \DB::raw("IFNULL(SUM(amount),0) as balance"),
                \DB::raw("sum(if(action='WITHDRAW' and status<>'REJECTED', amount*(-1), 0)) as withdrawn"),
                \DB::raw("sum(if(action='INCOME' and status<>'WITHDRAW_REJECT', amount, 0)) as income")
            )
            ->first();

        return json_response([
            'balance'   => array_get($account, 'balance', 0),
            'withdrawn' => array_get($account, 'withdrawn', 0),
            'income'    => array_get($account, 'income', 0)
        ]);
    }

    /**
     * @param MerchantAccount $merchant
     * @param $data
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function withdrawRequest(MerchantAccount $merchant, $data)
    {
        try {
            \DB::beginTransaction();
            $balance = MerchantTransaction::query()
                ->where('store_id', $merchant->id)
                ->lockForUpdate()
                ->select(\DB::raw("IFNULL(SUM(amount),0) as balance"))
                ->first();

            $balance = array_get($balance, 'balance', 0);

            $amount = array_get($data, 'amount', 1);

            if ($balance < $amount) {
                return json_response([], '余额不足提现哦~', 400);
            }

            // always be aware negative number
            if ($amount < 1) {
                return json_response([], '最低提现额度1元哦~', 400);
            }

            $accountInfo = [
                'account_type' => $data['account_type'],
                'account'      => $data['account'],
                'name'         => $data['name'],
                'phone'        => $data['phone'],
                'bank_name'    => array_get($data, 'bank_name', null),
            ];
            MerchantTransaction::withdrawApply([
                'store_id' => $merchant->id,
                'amount'   => $amount,
            ], array_filter($accountInfo));

            \DB::commit();
            return json_response([], '申请提现成功，请耐心等待审核~');
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception('服务器开小差,申请提现失败了~');
        }
    }

    /**
     * 流水明细
     * @param $merchant_id
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function list($merchant_id)
    {
        $transactions = MerchantTransaction::query()
            ->where('store_id', $merchant_id)
            ->orderBy('id', 'DESC')
            ->get();

        return json_response($transactions);
    }

    /**
     * 提现记录
     * @param $data
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function withdrawList($data)
    {
        $transactions = MerchantTransaction::query()
            ->where('store_id', $data['merchant_id'])
            ->whereIn('type', [MerchantTransaction::T_WITHDRAW, MerchantTransaction::T_WITHDRAW_REJECT])
            ->orderBy('id', 'DESC')
            ->get();

        return json_response($transactions);
    }

    /**
     * 提现记录
     * @param $data
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function withdrawList2($data)
    {
        if($data['merchant_id'] > 0) {
            $transactions = MerchantTransaction::query()
                ->where('store_id', $data['merchant_id'])
                ->whereIn('type', [MerchantTransaction::T_WITHDRAW, MerchantTransaction::T_WITHDRAW_REJECT])
                ->orderBy('id', 'DESC')
                ->get();
        }
        else{
            $transactions = MerchantTransaction::query()
                ->whereIn('type', [MerchantTransaction::T_WITHDRAW, MerchantTransaction::T_WITHDRAW_REJECT])
                ->orderBy('id', 'DESC')
                ->get();
        }

        return json_response($transactions);
    }

    /**
     * 提现记录+商户信息
     * @param $data
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function withdrawListWithInfo($data)
    {
        $query = MerchantTransaction::query()
            ->leftJoin('xsw_merchant_account', 'xsw_merchant_account.id', '=', 'xsw_merchant_transaction.store_id')
            ->leftJoin('xsw_merchant_info', 'xsw_merchant_info.account_id', '=', 'xsw_merchant_transaction.store_id')
            ->select([
                'xsw_merchant_transaction.*',
                'xsw_merchant_account.phone as merchant_phone',
                'xsw_merchant_account.name as merchant_name',
                'xsw_merchant_info.company_name'
            ]);
        $data['store_id'] && $query->where('xsw_merchant_transaction.store_id', $data['store_id']);
        $data['status'] && $query->where('xsw_merchant_transaction.status', $data['status']);

        $transactions = $query->orderBy('xsw_merchant_transaction.id', 'DESC')->get();

        return json_response($transactions);
    }

    /**
     * 提现审核
     * @param $data
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function auditWithdraw($data)
    {
        $statusMap = [
            'PASS' => MerchantTransaction::S_DONE,
            'REJECT' => MerchantTransaction::S_REJECTED
        ];

        $noteMap = [
            'PASS' => MerchantTransaction::N_WITHDRAW,
            'REJECT' => MerchantTransaction::N_WITHDRAW_REJECT
        ];
        /** @var MerchantTransaction $transaction */
        $transaction = MerchantTransaction::query()
            ->where('id', $data['id'])
            ->first();

        $transaction->update([
            'status' => $statusMap[$data['status']],
            'note'   => empty($data['note']) ? $noteMap[$data['status']] : $data['note']
        ]);

        return json_response();
    }
}
