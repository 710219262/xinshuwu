<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/7
 * Time: 11:13
 */

namespace App\Repos\User;

use App\Models\User;
use App\Models\UserVipOrder as VipOrder;
use App\Models\VipSendLog;
use Carbon\Carbon;
use Yansongda\Pay\Gateways\Alipay;
use Yansongda\Pay\Gateways\Wechat;

class UserVipOrder
{
    /**
     * 创建购买vip订单
     * @param User $user
     * @param $type
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function create(User $user, $type)
    {
        /** @var VipOrder $order */
        $order = VipOrder::query()
            ->create([
                'user_id'    => $user->id,
                'order_no'   => VipOrder::newOrderNum($user->phone),
                'type'       => $type,
                'price'      => VipOrder::getCardPriceByType($type),
                'pay_amount' => VipOrder::getCardPriceByType($type),
            ]);
        return json_response($order);
    }

    /**
     * 支付成功
     * @param $orderNo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public static function paid($orderNo)
    {
        try {
            \DB::beginTransaction();
            /** @var VipOrder $order */
            $order = VipOrder::query()
                ->where('order_no', $orderNo)
                ->where('status', VipOrder::S_CREATED)
                ->sharedLock()
                ->first();

            $order && $order->update(['status' => VipOrder::S_PAYED]);
            $order && $user = User::query()->where('id', $order->user_id)->sharedLock()->first();
            $monthMap = [
                VipOrder::T_MONTH  => 1,
                VipOrder::T_SEASON => 3,
                VipOrder::T_YEAR   => 12,
            ];
            if (!empty($user)) {
                $user->update([
                    'vip_card' => $user->vip_card ?
                        Carbon::createFromTimeString($user->vip_card)->addMonth($monthMap[$order->type]) :
                        Carbon::now()->addMonth($monthMap[$order->type])
                ]);
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('vip card order change status failed', ['order_no' => $orderNo]);
        }
        return json_response([]);
    }

    /**
     * 微信预支付
     * @param User $user
     * @param $orderNo
     * @return string
     */
    public function prepayViaWechat(User $user, $orderNo, $openid = '')
    {
        /** @var Wechat $wechat */
        $wechat = app('pay.wechat');
        $order  = VipOrder::query()
            ->where('order_no', $orderNo)
            ->where('user_id', $user->id)
            ->first();

        $order->update([
            'pay_method' => VipOrder::P_WECHAT
        ]);

        $data = [
            'notify_url'   => config('xsw.vip_member.wechat_notify_url'),
            'out_trade_no' => $order->order_no,
            'body'         => VipOrder::DEFAULT_BODY,
            'total_fee'    => yuan_to_fen($order->pay_amount),
        ];
        return json_response(json_decode($wechat->app($data)->getContent()));
    }

    /**
     * 支付宝预支付
     * @param User $user
     * @param $orderNo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function prepayViaAlipay(User $user, $orderNo, $openid = '')
    {
        /** @var Alipay $alipay */
        $alipay = app('pay.alipay');
        $order  = VipOrder::query()
            ->where('order_no', $orderNo)
            ->where('user_id', $user->id)
            ->first();

        $order->update([
            'pay_method' => VipOrder::P_ALI
        ]);

        $data = [
            'notify_url'      => config('xsw.vip_member.ali_notify_url'),
            'out_trade_no'    => $order->order_no,
            'subject'         => VipOrder::DEFAULT_BODY,
            'total_amount'    => sprintf("%.2f", $order->pay_amount),
            'timeout_express' => '30m',
        ];

        return json_response($alipay->app($data)->getContent());
    }

    /**
     * 微信公众号预支付
     * @param User $user
     * @param $orderNo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function prepayViaWechat_jsapi(User $user, $orderNo, $openid = '')
    {
        /** @var Wechat $wechat */
        $wechat = app('pay.wechat');
        $order  = VipOrder::query()
            ->where('order_no', $orderNo)
            ->where('user_id', $user->id)
            ->first();

        $order->update([
            'pay_method' => VipOrder::P_WECHAT
        ]);
        $data = [
            'notify_url'   => config('xsw.vip_member.wechat_notify_url'),
            'out_trade_no' => $order->order_no,
            'body'         => VipOrder::DEFAULT_BODY,
            'total_fee'    => yuan_to_fen($order->pay_amount),
            'openid' => $openid,
        ];
        $res = json_decode($wechat->mp($data));
        $res->pay_amount = $order->pay_amount;
        return json_response($res);
    }


    public static function send($user_id, $type)
    {
        try {
            \DB::beginTransaction();
            $user = User::query()->where('id', $user_id)->sharedLock()->first();
            $monthMap = [
                VipOrder::T_MONTH  => 1,
                VipOrder::T_SEASON => 3,
                VipOrder::T_YEAR   => 12,
            ];
            //添加日志
            $info['user_id'] = $user_id;
            $info['price']   = VipOrder::getCardPriceByType($type);
            $info['status'] = 'SENDED';
            $info['type'] = 'GUESS';
            VipSendLog::query()->create($info);
            if (!empty($user)) {
                $user->update([
                    'vip_card' => $user->vip_card ?
                        Carbon::createFromTimeString($user->vip_card)->addMonth($monthMap[$type]) :
                        Carbon::now()->addMonth($monthMap[$type])
                ]);
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('vip card order change status failed by send');
        }
        return json_response([]);
    }
}