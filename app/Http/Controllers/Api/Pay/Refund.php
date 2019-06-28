<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/16
 * Time: 17:47
 */

namespace App\Http\Controllers\Api\Pay;

use App\Models\AfterSaleOrder;
use Yansongda\Pay\Gateways\Wechat;

class Refund
{
    /**
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    public function wechatNotify()
    {
        /** @var Wechat $wechat */
        $wechat = app('pay.wechat');
        $data   = $wechat->verify(null, true);

        AfterSaleOrder::refundSuccess($data->get('out_refund_no'));

        \Log::info("refund_wechat_notify", $data->toArray());

        $wechat->success();
    }
}
