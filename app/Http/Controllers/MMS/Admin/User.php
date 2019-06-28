<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/9
 * Time: 16:51
 */

namespace App\Http\Controllers\MMS\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserTransaction;
use App\Repos\User\TransactionRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Repos\Admin\UserRepo;
class User extends Controller
{
    /**
     * 用户提现列表
     * @param Request $request
     * @param TransactionRepo $transactionRepo
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
                    UserTransaction::S_AUDIT_PENDING,
                    UserTransaction::S_REJECTED,
                    UserTransaction::S_PENDING,
                    UserTransaction::S_DONE,
                ]),
            ],
        ]);

        return $transactionRepo->withdrawListWithInfo([
            'user_id'  => $request->input('id'),
            'status'   => $request->input('status'),
        ]);
    }

    /**
     * 用户提现审核
     * @param Request $request
     * @param TransactionRepo $transactionRepo
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function auditWithdraw(Request $request, TransactionRepo $transactionRepo)
    {
        $this->validate($request, [
            'id'     => [
                'required',
                'string',
                Rule::exists('xsw_user_transaction', 'id')
                    ->where('status', UserTransaction::S_AUDIT_PENDING),
            ],
            'status' => 'required|in:REJECT,PASS',
        ]);

        return $transactionRepo->auditWithdraw([
            'id'     => $request->input('id'),
            'status' => $request->input('status'),
            'note'   => $request->input('note'),
        ]);
    }

    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request, UserRepo $userRepo)
    {
        if(!empty($request->input('ispage'))) {
            $pageSize = empty($request->input('pageSize')) ? 10 : $request->input('pageSize');
            $pageIndex = empty($request->input('pageIndex')) ? 1 : $request->input('pageIndex');
            $offset = ceil($pageSize * ($pageIndex - 1));
            $videos = $userRepo->list($request->input('query'), $offset, $pageSize);
        }else{
            $videos = $userRepo->list($request->input('query'));
        }

        return json_response($videos);
    }
}
