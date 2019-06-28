<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/16
 * Time: 15:54
 */

namespace App\Events\Order;

use App\Models\AfterSaleOrder;

class AfterSaleRefund
{
    public $order;

    public function __construct(AfterSaleOrder $order)
    {
        $this->order = $order;
    }
}
