<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/7
 * Time: 11:09
 */

namespace App\Http\Controllers\Api\User;


use App\Http\Controllers\Controller;
use App\Models\UserVipOrder;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class VipOrder extends Controller
{
    /**
     * @param Request $request
     * @param \App\Repos\User\UserVipOrder $orderRepo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, \App\Repos\User\UserVipOrder $orderRepo)
    {
        $this->validate($request, [
            'type' => [
                'required',
                'string',
                Rule::in([
                    UserVipOrder::T_MONTH,
                    UserVipOrder::T_SEASON,
                    UserVipOrder::T_YEAR,
                ])
            ]
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        return $orderRepo->create($user, $request->input('type'));
    }

    /**
     * @param Request $request
     * @param \App\Repos\User\UserVipOrder $orderRepo
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function prepay(Request $request, \App\Repos\User\UserVipOrder $orderRepo)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->validate($request, [
            'order_no' => [
                'required',
                'string',
                Rule::exists('xsw_user_vip_order', 'order_no')
                    ->where('user_id', $user->id)
                    ->where('status', UserVipOrder::S_CREATED),
            ],
            'pay_method' => [
                'required',
                'string',
                Rule::in(
                    UserVipOrder::P_ALI,
                    UserVipOrder::P_WECHAT,
                    UserVipOrder::P_WECHAT_JSAPI
                ),

            ]
        ], [
            'order_no.exists' => '订单不翼而飞了哦~',
        ]);
        $openid = '';
        if (UserVipOrder::P_WECHAT_JSAPI === strtoupper($request->input('pay_method'))) {
            $this->validate($request, [
                'openid' => [
                    'required',
                    'string',
                ],
            ]);
            $openid = $request->input('openid');
        }
        return $orderRepo->{'prepayVia' . ucfirst($request->input('pay_method'))}($user, $request->input('order_no'), $openid);
    }
}