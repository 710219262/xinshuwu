<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 27/03/2019
 * Time: 14:20
 */

namespace App\Http\Controllers\Api\Pay;

use App\Events\Order\OrderWasUpdated;
use App\Http\Controllers\Controller;
use App\Models\AfterSaleOrder;
use App\Models\Order;
use Illuminate\Database\Query\Builder;
use Yansongda\Pay\Gateways\Alipay;
use Yansongda\Pay\Gateways\Wechat;
use Yansongda\Supports\Collection;

class PayController extends Controller
{
    /**
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    public function wechatNotify()
    {
        /** @var Wechat $wechat */
        $wechat = app('pay.wechat');
        
        // assure callback is authorized
        $data = $wechat->verify();
        $orderNo = $data->get('out_trade_no');
        $attach = @json_decode($data->get('attach'), true);
        $isBatch = array_get($attach, 'is_batch', false);
        $column = $isBatch ? 'batch_no' : 'order_no';
        
        \Log::info("wechat_notify", $data->toArray());
        
        /** @var Order $order */
        $order = Order::query()->where($column, $orderNo)->get();
        
        \Log::info('wechat update info', [
            'is_batch' => $isBatch,
            'attach'   => $attach,
            'column'   => $column,
            'order_no' => $orderNo,
            'order'    => $order->toArray(),
        ]);
        
        $this->changeOrderStatus($column, $orderNo);
        
        return $wechat->success();
    }
    
    /**
     * @return mixed
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    public function alipayNotify()
    {
        /** @var Alipay $alipay */
        $alipay = app('pay.alipay');
        
        // assure callback is authorized
        $data = $alipay->verify();
        
        $notifyType = $data->get('refund_fee') ? 'REFUND' : 'PAY';
        
        if ($notifyType === 'REFUND') {
            $this->refundNotify($data);
        } else {
            $this->payNotify($data);
        }
        
        return $alipay->success();
    }
    
    /**
     * 支付宝支付回调处理
     *
     * @param Collection $data
     */
    protected function payNotify(Collection $data)
    {
        $orderNo = $data->get('out_trade_no');
        $params = @json_decode($data->get('passback_params'), true);
        $isBatch = array_get($params, 'is_batch', false);
        
        $column = $isBatch ? 'batch_no' : 'order_no';
        
        \Log::info("ali_pay_notify", $data->toArray());
        
        $this->changeOrderStatus($column, $orderNo);
    }
    
    /**
     * 支付宝退款回调处理
     *
     * @param Collection $data
     */
    protected function refundNotify(Collection $data)
    {
        AfterSaleOrder::refundSuccess($data->get('out_biz_no'));
        
        \Log::info("ali_refund_notify", $data->toArray());
    }
    
    /**
     * @param $column
     * @param $orderNo
     */
    protected function changeOrderStatus($column, $orderNo)
    {
        /** @var Builder $builder */
        $builder = Order::query()
            ->where('status', Order::S_CREATED)
            ->where($column, $orderNo);
        
        if ($builder->count() > 0) {
            $builder->update([
                'status' => Order::S_PAYED,
            ]);
            
            $orders = Order::query()
                ->where($column, $orderNo)
                ->get();
            
            /** @var Order $order */
            foreach ($orders as $order) {
                event(new OrderWasUpdated($order));
            }
        }
    }
}
