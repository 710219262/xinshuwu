<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/13
 * Time: 15:43
 */

namespace App\Http\Controllers\Api\Pay;

use App\Models\MerchantTransaction;
use App\Models\UserTransaction;
use Yansongda\Pay\Gateways\Alipay;

class Transfer
{
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
        $attach = json_decode($data->get('attach', ''), true);
        $orderNo = $data->get('out_trade_no');

        \Log::info("支付宝提现回发记录", $data->toArray());

        switch (array_get($attach, 'type')) {
            case 'MERCHANT':
                 MerchantTransaction::withdrawFinished($orderNo);
                break;
            case 'USER':
                 UserTransaction::withdrawFinished($orderNo);
                break;
            default:
                break;
        }

        return $alipay->success();
    }
}
