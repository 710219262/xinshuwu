<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 29/04/2019
 * Time: 22:15
 */

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserTransaction;
use App\Repos\Redis\UserAuthRepo;
use App\Repos\User\TransactionRepo;
use App\Services\AuthService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Transaction extends Controller
{
    
    /**
     * @param Request         $request
     *
     * @param TransactionRepo $transactionRepo
     *
     * @param UserAuthRepo    $userAuthRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function withDraw(
        Request $request,
        TransactionRepo $transactionRepo,
        UserAuthRepo $userAuthRepo
    ) {
        $user = $request->user();
        
        $this->validate($request, [
            'amount'  => 'required|numeric|min:1',
            'code'    => 'required|string|size:4',
            'account' => 'required|string',
            'name'    => 'required|string',
            'method'  => [
                'required',
                'string',
                // Rule::in([UserTransaction::M_ALIPAY, UserTransaction::M_BANKCARD, UserTransaction::M_WECHAT]),
                // only support alipay now
                Rule::in([UserTransaction::M_ALIPAY]),
            ],
        ], [
            'amount.min' => '最低提现额度1元哦~',
        ]);
        
        $phone = $user->phone;
        
        $code = $userAuthRepo->getVerifyCode($phone);
        
        if ($userAuthRepo->getAuthFrq($phone) > AuthService::USER_MAX_FAILED_PER_HOUR) {
            return json_response([], '你尝试次数过多，请稍后再试', 429);
        }
        
        if ($code !== $request->input('code')) {
            $userAuthRepo->incAuthFrq($phone);
            return json_response([], '验证码输入错误', 401);
        }
        
        return $transactionRepo->withdrawRequest(
            $user,
            $request->only([
                'account',
                'name',
                'method',
                'amount'
            ])
        );
    }
    
    /**
     * @param Request         $request
     *
     * @param TransactionRepo $transactionRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function balance(Request $request, TransactionRepo $transactionRepo)
    {
        return json_response([
            'balance' => $transactionRepo->balance(
                $request->user()
            ),
        ]);
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function code(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        $code = generateVerifyCode();
        
        return SmsService::sendVerifyCode(
            $user->phone,
            $code
        );
    }
}
