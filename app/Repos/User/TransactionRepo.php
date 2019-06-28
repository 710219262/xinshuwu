<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 27/04/2019
 * Time: 23:01
 */

namespace App\Repos\User;

use App\Events\Transaction\UserWithdrawAudited;
use App\Models\User;
use App\Models\UserTransaction;
use DB;
use Exception;
use Illuminate\Http\Request;

class TransactionRepo
{
    /**
     * @param User $user
     *
     * @return mixed
     */
    public function balance(User $user)
    {
        $balance = UserTransaction::query()
            ->where('user_id', $user->id)
            ->sharedLock()
            ->select(DB::raw("IFNULL(SUM(amount),0) as balance"))
            ->first();
        
        return array_get($balance, 'balance', 0);
    }
    
    /**
     * @param User $user
     * @param      $data
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws Exception
     */
    public function withdrawRequest(User $user, $data)
    {
        try {
            DB::beginTransaction();
            $balance = UserTransaction::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->select(DB::raw("IFNULL(SUM(amount),0) as balance"))
                ->first();

            $balance = array_get($balance, 'balance', 0);
            
            $amount = array_get($data, 'amount', 1);
            $method = array_get($data, 'method', UserTransaction::M_ALIPAY);

            if ($balance < $amount) {
                return json_response([], '余额不足提现哦~', 400);
            }
            
            // always be aware negative number
            if ($amount < 1) {
                return json_response([], '最低提现额度1元哦~', 400);
            }
            UserTransaction::withdrawApply([
                'user_id' => $user->id,
                'amount'  => $amount,
            ], [
                'account_type' => $method,
                'account'      => $data['account'],
                'name'         => $data['name']
            ]);
            
            DB::commit();
            return json_response([], '申请提现成功，请耐心等待审核~');
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('服务器开小差,申请提现失败了~');
        }
    }
    
    /**
     * @return array
     */
    protected function buildPayload()
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
     * 提现记录+用户信息
     * @param $data
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function withdrawListWithInfo($data)
    {
        $query = UserTransaction::query()
            ->leftJoin('xsw_user', 'xsw_user.id', '=', 'xsw_user_transaction.user_id')
            ->select([
                'xsw_user_transaction.*',
                'xsw_user.phone as user_phone',
                'xsw_user.nickname as user_nickname'
            ]);
        $data['user_id'] && $query->where('xsw_user_transaction.user_id', $data['user_id']);
        $data['status'] && $query->where('xsw_user_transaction.status', $data['status']);
        $query->where('xsw_user_transaction.action', 'WITHDRAW');
        $transactions = $query->orderBy('xsw_user_transaction.id', 'DESC')->get();

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
            'PASS' => UserTransaction::S_PENDING,
            'REJECT' => UserTransaction::S_REJECTED
        ];

        $noteMap = [
            'PASS' => UserTransaction::N_WITHDRAW,
            'REJECT' => UserTransaction::N_WITHDRAW_REJECT
        ];

        /** @var UserTransaction $transaction */
        $transaction = UserTransaction::query()
            ->where('id', $data['id'])
            ->first();

        $transaction->update([
            'status' => $statusMap[$data['status']],
            'note'   => empty($data['note']) ? $noteMap[$data['status']] : $data['note']
        ]);

        event(new UserWithdrawAudited($transaction));

        return json_response();
    }
}
