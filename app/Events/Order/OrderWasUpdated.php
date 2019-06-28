<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 29/03/2019
 * Time: 17:44
 */

namespace App\Events\Order;

use App\Events\Event;
use App\Models\Order;

class OrderWasUpdated extends Event
{
    /**
     * @var Order $order
     */
    public $order;
    
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
