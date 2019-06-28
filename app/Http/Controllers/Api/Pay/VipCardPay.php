<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/7
 * Time: 13:10
 */

namespace App\Http\Controllers\Api\Pay;

use App\Http\Controllers\Controller;
use Yansongda\Pay\Gateways\Alipay;
use Yansongda\Pay\Gateways\Wechat;

class VipCardPay extends Controller
{
    /**
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     * @throws \Exception
     */
    public function wechatNotify()
    {
        /** @var Wechat $wechat */
        $wechat = app('pay.wechat');

        // assure callback is authorized
        $data = $wechat->verify();
        $orderNo = $data->get('out_trade_no');

        \Log::info("vip_card_wechat_notify", $data->toArray());

        \App\Repos\User\UserVipOrder::paid($orderNo);
        return $wechat->success();
    }

    /**
     * @return mixed
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     * @throws \Exception
     */
    public function alipayNotify()
    {
        /** @var Alipay $alipay */
        $alipay = app('pay.alipay');

        // assure callback is authorized
        $data = $alipay->verify();
        $orderNo = $data->get('out_trade_no');


        \Log::info("vip_card_ali_notify", $data->toArray());

        \App\Repos\User\UserVipOrder::paid($orderNo);
        return $alipay->success();
    }
}
