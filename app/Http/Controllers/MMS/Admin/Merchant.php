<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 07/03/2019
 * Time: 16:37
 */

namespace App\Http\Controllers\MMS\Admin;

use App\Http\Controllers\Controller;
use App\Models\MerchantAccount;
use App\Models\MerchantTransaction;
use App\Repos\Admin\MerchantInfoRepo;
use App\Repos\Merchant\TransactionRepo;
use Illuminate\Http\Request;
use App\Services\SmsService;
use Illuminate\Validation\Rule;

class Merchant extends Controller
{
    /**
     * 获取商户列表
     *
     * @param Request          $request
     * @param MerchantInfoRepo $merchantInfoRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getMerchantList(Request $request, MerchantInfoRepo $merchantInfoRepo)
    {
        $this->validate($request, [
            'status' => 'string|in:unchecked',
        ]);
        if ('unchecked' === $request->input('status')) {
            $status = [MerchantAccount::S_CHECKING, MerchantAccount::S_UNCHECKED];
        } else {
            $status = MerchantAccount::S_CHECKED;
        }
        
        $list = $merchantInfoRepo->getList($status);
        return json_response($list);
    }
    
    /**
     * @param Request         $request
     * @param TransactionRepo $transactionRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function transactionList(Request $request, TransactionRepo $transactionRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_merchant_account,id',
        ]);

        return $transactionRepo->withdrawList([
            'merchant_id' => $request->input('id'),
        ]);
    }

    public function transactionList2(Request $request, TransactionRepo $transactionRepo)
    {
        $this->validate($request, [
            'id' => 'int',
        ]);

        return $transactionRepo->withdrawList2([
            'merchant_id' => $request->input('id'),
        ]);
    }

    /**
     * 获取商户列表
     *
     * @param Request          $request
     * @param MerchantInfoRepo $merchantInfoRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getStoreList(Request $request, MerchantInfoRepo $merchantInfoRepo)
    {
        $this->validate($request, [
            'status' => 'string',
        ]);
        if (!empty($request->input('ispage'))) {
            $pageSize = empty($request->input('pageSize')) ? 10 : $request->input('pageSize');
            $pageIndex = empty($request->input('pageIndex')) ? 1 : $request->input('pageIndex');
            $offset = ceil($pageSize * ($pageIndex - 1));
            $list = $merchantInfoRepo->getList($request->input('query'), $offset, $pageSize);
        } else {
            $list = $merchantInfoRepo->getList($request->input('query'));
        }
        return json_response($list);
    }
    
    /**
     * @param Request          $request
     *
     * @param MerchantInfoRepo $merchantInfoRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getMerchantInfo(Request $request, MerchantInfoRepo $merchantInfoRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_merchant_info,id',
        ]);
        
        $info = $merchantInfoRepo->getMerchantInfo($request->input('id'));
        return json_response($info);
    }
    
    /**
     * 审核商户信息
     *
     * @param Request          $request
     * @param MerchantInfoRepo $merchantInfoRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function checkMerchant(Request $request, MerchantInfoRepo $merchantInfoRepo)
    {
        $this->validate($request, [
            'id'     => 'required|int|exists:xsw_merchant_info,id',
            'result' => 'required|in:PASS,REJECT',
            'reason' => 'required_if:result,REJECT|string',
        ]);
        
        $merchantInfoRepo->checkOrReject(
            $request->input('id'),
            $request->input('result'),
            $request->input('reason')
        );
        
        if ($request->input('result') === 'PASS') {
            $merchant = $merchantInfoRepo->getMerchantInfo($request->input('id'));
            SmsService::sendCheckMerchant(
                $merchant['phone']
            );
        }
        return json_response([], '提交成功');
    }
    
    /**'
     * @param Request         $request
     * @param TransactionRepo $transactionRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getWithdrawList(Request $request, TransactionRepo $transactionRepo)
    {
        $this->validate($request, [
            'id'     => 'string|exists:xsw_merchant_account,id',
            'status' => [
                'string',
                Rule::in([
                    MerchantTransaction::S_AUDIT_PENDING,
                    MerchantTransaction::S_REJECTED,
                    MerchantTransaction::S_PENDING,
                    MerchantTransaction::S_DONE,
                ]),
            ],
        ]);

        return $transactionRepo->withdrawListWithInfo([
            'store_id' => $request->input('id'),
            'status'   => $request->input('status'),
        ]);
    }

    /**'
     * @param Request         $request
     * @param TransactionRepo $transactionRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getWithdrawList2(Request $request, TransactionRepo $transactionRepo)
    {
        $this->validate($request, [
            'status' => [
                'string',
                Rule::in([
                    MerchantTransaction::S_AUDIT_PENDING,
                    MerchantTransaction::S_REJECTED,
                    MerchantTransaction::S_PENDING,
                    MerchantTransaction::S_DONE,
                ]),
            ],
        ]);

        return $transactionRepo->withdrawListWithInfo([
            'status'   => $request->input('status'),
        ]);
    }

    /**
     * 商户提现审核
     *
     * @param Request         $request
     * @param TransactionRepo $transactionRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function auditWithdraw(Request $request, TransactionRepo $transactionRepo)
    {
        $this->validate($request, [
            'id'     => [
                'required',
                'string',
                Rule::exists('xsw_merchant_transaction', 'id')
                    ->where('status', MerchantTransaction::S_AUDIT_PENDING),
            ],
            'status' => 'required|in:REJECT,PASS',
        ]);
        
        return $transactionRepo->auditWithdraw([
            'id'     => $request->input('id'),
            'status' => $request->input('status'),
            'note'   => $request->input('note'),
        ]);
    }
}
