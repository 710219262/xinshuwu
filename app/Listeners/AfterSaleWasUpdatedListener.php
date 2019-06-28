<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/15
 * Time: 17:18
 */

namespace App\Listeners;

use App\Events\Order\AfterSaleRefund;
use App\Events\Order\AfterSaleWasUpdated;
use App\Models\AfterSaleOrder;

class AfterSaleWasUpdatedListener
{
    /**
     * @var AfterSaleOrder $order
     */
    protected $order;

    /**
     * @param AfterSaleWasUpdated $event
     * @throws \Exception
     */
    public function handle(AfterSaleWasUpdated $event)
    {
        $this->order = $event->order;

        switch ($this->order->status) {
            case AfterSaleOrder::S_AGREED:
                $this->handleAgreed();
                break;
            case AfterSaleOrder::S_REJECTED:
            case AfterSaleOrder::S_REQUEST:
            case AfterSaleOrder::S_SHIPPING:
            case AfterSaleOrder::S_CANCEL:
            case AfterSaleOrder::S_PROCESSING:
            case AfterSaleOrder::S_COMPLETED:
                break;
            case AfterSaleOrder::S_RECEIVED:
                $this->handleRevived();
                break;
            default:
                break;
        }
    }

    protected function handleAgreed()
    {
        if ($this->order->type === AfterSaleOrder::T_REFUND) {
            event(new AfterSaleRefund($this->order));
        }
    }

    protected function handleRevived()
    {
        event(new AfterSaleRefund($this->order));
    }
}
