<?php
/**
 * Created by PhpStorm.
 * User: june
 * Date: 19-5-20
 * Time: 下午9:07
 */

namespace App\Console\Commands;

use App\Logics\Order\OrderLogic;
use App\Models\AfterSaleOrder;
use App\Models\Order;
use App\Repos\Merchant\AfterSaleRepo;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoReceive extends Command
{
    protected $signature = 'auto_receive';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->userReceive();
        $this->merchantReceive();
    }

    /**
     * 用户自动收货
     * @throws \Exception
     */
    protected function userReceive()
    {
        $orders = Order::query()
            ->where('status', '=', Order::S_SHIPPED)
            ->where('created_at', '<', Carbon::now()->addDays(-15))
            ->get();
        /** @var Order $order */
        foreach ($orders as $order) {
            /** @var OrderLogic $orderLogic */
            $orderLogic = app(OrderLogic::class);
            $orderLogic->receiveOrder($order->order_no);
        }
    }

    /**
     * 商家自动收货
     */
    protected function merchantReceive()
    {
        $orders = AfterSaleOrder::query()
            ->where('status', '=', AfterSaleOrder::S_SHIPPING)
            ->where('dispatch_at', '<', Carbon::now()->addDays(-15))
            ->get();

        /** @var AfterSaleRepo $afterSaleRepo */
        $afterSaleRepo = app(AfterSaleRepo::class);
        /** @var AfterSaleOrder $order */
        foreach ($orders as $order) {
            $afterSaleRepo->receive([
                'store_id'     => $order->store_id,
                'aftersale_no' => $order->aftersale_no
            ]);
        }
    }
}
