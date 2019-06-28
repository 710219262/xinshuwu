<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/9
 * Time: 16:21
 */

namespace App\Http\Controllers\MMS\Merchant;

use App\Http\Controllers\Controller;
use App\Models\MerchantAccount;
use App\Models\MerchantTransaction;
use App\Repos\Merchant\TransactionRepo;
use App\Repos\Redis\MerchantAuthRepo;
use App\Services\AuthService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Transaction extends Controller
{
    /**
     * @param Request $request
     * @param TransactionRepo $transactionRepo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function list(Request $request, TransactionRepo $transactionRepo)
    {
        return $transactionRepo->list([
            'user_id' => $request->user()->id
        ]);
    }

    /**
     * @param Request $request
     * @param TransactionRepo $transactionRepo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function withdrawList(Request $request, TransactionRepo $transactionRepo)
    {
        return $transactionRepo->withdrawList([
            'merchant_id'      => $request->user()->id
        ]);
    }

    /**
     * @param Request $request
     * @param TransactionRepo $transactionRepo
     * @param MerchantAuthRepo $authRepo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function withDraw(
        Request $request,
        TransactionRepo $transactionRepo,
        MerchantAuthRepo $authRepo
    ) {
        $this->validate($request, [
            'code'    => 'required|string|size:4',
            'amount'  => 'required|numeric|min:1',
            'account_type' => [
                'required',
                'string',
                Rule::in([
                    MerchantTransaction::M_ALIPAY,
                    MerchantTransaction::M_PERSONAL_BANKCARD,
                    MerchantTransaction::M_COMPANY_BANKCARD,
                ])
            ],
            'account'      => 'required|string',
            'name'         => 'required|string',
            'phone'        => 'required|string',
            'bank_name'    => 'required_unless:account_type,ALIPAY|string'
        ], [
            'amount.min' => '最低提现额度1元哦~',
        ]);

        $phone = $request->user()->phone;
        $code = $authRepo->getVerifyCode($phone);

        if ($authRepo->getAuthFrq($phone) > AuthService::MMS_MAX_FAILED_PER_HOUR) {
            return json_response([], '你尝试次数过多，请稍后再试', 429);
        }

        if ($code !== $request->input('code')) {
            $authRepo->incAuthFrq($phone);
            return json_response([], '验证码输入错误', 401);
        }

        return $transactionRepo->withdrawRequest(
            $request->user(),
            $request->only([
                'amount',
                'account_type',
                'account',
                'name',
                'phone',
                'bank_name',
            ])
        );
    }

    /**
     * @param Request $request
     *
     * @param TransactionRepo $transactionRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function account(Request $request, TransactionRepo $transactionRepo)
    {
        return $transactionRepo->account($request->user());
    }

    public function accountToday(Request $request, TransactionRepo $transactionRepo)
    {
        return $transactionRepo->accountToday($request->user());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function code(Request $request)
    {
        /** @var MerchantAccount $user */
        $user = $request->user();

        $code = generateVerifyCode();

        return SmsService::sendVerifyCode(
            $user->phone,
            $code
        );
    }
}
