<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 24/04/2019
 * Time: 22:56
 */

namespace App\Listeners;

use App\Events\Order\OrderWasReceived;
use App\Events\Order\OrderWasUpdated;
use App\Models\MerchantTransaction;
use App\Models\Order;
use App\Models\PlatformTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Repos\Admin\MerchantInfoRepo;
use App\Services\SmsService;

class OrderWasUpdatedListener implements ShouldQueue
{
    /**
     * @var Order $order
     */
    protected $order;

    /**
     * @param OrderWasUpdated $event
     */
    public function handle(OrderWasUpdated $event)
    {
        $this->order = $event->order;
        $this->dispatchHandleByStatus();
    }

    public function dispatchHandleByStatus()
    {
        $status = $this->order->status;

        switch ($status) {
            case Order::S_PAYED:
                // notify merchant delivery goods ASAP and record transaction water into platform account
                $this->handlePayed();
                break;
            case Order::S_RECEIVED:
                // platform transfer frozen money to merchant account after deducting platform service fee
                $this->handleReceived();
                break;
            case Order::S_CANCELED:
                // notify user order pay timeout
                break;
        }
    }

    protected function handlePayed()
    {
        PlatformTransaction::payIn([
            'type'       => PlatformTransaction::TYPE_PAY_GOODS,
            'target'     => PlatformTransaction::T_USER,
            'note'       => PlatformTransaction::N_USER_PAY,
            'target_id'  => $this->order->user_id,
            'refer_id'   => $this->order->id,
            'amount'     => $this->order->pay_amount,
        ], [
            'pay_method' => $this->order->pay_method,
        ]);
        $merchantInfoRepo = new MerchantInfoRepo();
        $merchant = $merchantInfoRepo->getMerchantAccount($this->order->store_id);
        SmsService::sendMerchantOrder(
            $merchant['phone']
        );
    }

    protected function handleReceived()
    {
        event(new OrderWasReceived($this->order));
    }
}
