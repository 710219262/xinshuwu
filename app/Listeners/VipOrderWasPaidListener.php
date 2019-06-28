<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/8
 * Time: 10:02
 */

namespace App\Listeners;

use App\Events\Order\VipOrderWasPaid;
use App\Models\PlatformTransaction;
use App\Models\UserVipOrder;
use Illuminate\Contracts\Queue\ShouldQueue;

class VipOrderWasPaidListener implements ShouldQueue
{
    public function handle(VipOrderWasPaid $event)
    {

        /** @var UserVipOrder $order */
        $order = $event->order;

        PlatformTransaction::payIn(
            [
                'target'     => PlatformTransaction::T_USER,
                'note'       => PlatformTransaction::N_USER_PAY_VIP,
                'type'       => PlatformTransaction::TYPE_PAY_VIP,
                'target_id'  => $order->user_id,
                'refer_id'   => $order->id,
                'amount'     => $order->pay_amount,
                'pay_method' => $order->pay_method,
            ]
        );
    }
}
