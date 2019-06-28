<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/16
 * Time: 15:54
 */

namespace App\Listeners;

use App\Events\Order\AfterSaleRefund;
use App\Models\AfterSaleOrder;
use App\Models\Order;
use App\Models\PlatformTransaction;
use Yansongda\Pay\Gateways\Alipay;
use Yansongda\Pay\Gateways\Wechat;

class AfterSaleRefundListener
{
    /**
     * @var AfterSaleOrder $order
     */
    protected $afterOrder;
    
    /**
     * @var Order $order
     */
    protected $order;
    
    protected $refundAmount;
    
    /**
     * @param AfterSaleRefund $event
     *
     * @throws \Exception
     */
    public function handle(AfterSaleRefund $event)
    {
        try {
            \DB::beginTransaction();
            $this->afterOrder = AfterSaleOrder::query()->lockForUpdate()
                ->where('id', $event->order->id)
                ->first();
            
            $this->order = $this->afterOrder->order;
            
            if ($this->order->pay_method === Order::P_ALI) {
                $this->alipayRefund();
            } else {
                $this->wechatRefund();
            }
            
            $this->changeOrderStatus();
            $this->platformPayout();
            
            \DB::commit();
        } catch (\Exception $e) {
            \Log::error('退款处理异常', [
                'err_msg' => $e->getMessage(),
                'trace'   => $e->getTrace(),
            ]);
            \DB::rollBack();
        }
    }
    
    
    /**
     * 支付宝退款
     *
     * @throws \Yansongda\Pay\Exceptions\GatewayException
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    protected function alipayRefund()
    {
        /** @var Alipay $alipay */
        $alipay = app('pay.alipay');
        
        $alipay->refund([
            'out_trade_no'   => $this->getOutTradeNo(),
            'refund_amount'  => $this->afterOrder->refund_amount,
            'refund_reason'  => '猩事物售后退款',
            'out_request_no' => $this->afterOrder->aftersale_no,
        ]);
    }
    
    /**
     * 微信退款
     *
     * @throws \Yansongda\Pay\Exceptions\GatewayException
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    protected function wechatRefund()
    {
        /** @var Wechat $wechat */
        $wechat = app('pay.wechat');
        
        $totalAmount = Order::query()
            ->where('batch_no', $this->order->batch_no)
            ->sum('pay_amount');
        
        $wechat->refund([
            'notify_url'    => config('xsw.aftersale.wechat_notify_url'),
            'out_trade_no'  => $this->getOutTradeNo(),
            'out_refund_no' => $this->afterOrder->aftersale_no,
            'total_fee'     => yuan_to_fen($totalAmount),
            'refund_fee'    => yuan_to_fen($this->afterOrder->refund_amount),
            'refund_desc'   => '猩事物售后退款',
        ]);
    }
    
    /**
     * 获取订单编号
     *
     * @return int
     */
    protected function getOutTradeNo()
    {
        $isBatch = Order::query()->where('batch_no', $this->order->batch_no)->count() > 1;
        
        return $isBatch ? $this->order->batch_no : $this->order->order_no;
    }
    
    /**
     * 修改售后订单状态
     */
    protected function changeOrderStatus()
    {
        AfterSaleOrder::query()
            ->where('aftersale_no', $this->afterOrder->aftersale_no)
            ->update([
                'status' => AfterSaleOrder::S_PROCESSING,
            ]);
    }
    
    /**
     * 平台生成退款交易流水
     */
    protected function platformPayout()
    {
        PlatformTransaction::payOut([
            'type'      => PlatformTransaction::TYPE_REFUND,
            'target'    => PlatformTransaction::T_PLATFORM,
            'target_id' => '',
            'refer_id'  => $this->afterOrder->id,
            'amount'    => $this->afterOrder->refund_amount,
            'note'      => PlatformTransaction::N_REFUND,
        ]);
    }
}
