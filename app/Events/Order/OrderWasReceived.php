<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/8
 * Time: 16:31
 */

namespace App\Events\Order;

use App\Models\Order;

class OrderWasReceived
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
