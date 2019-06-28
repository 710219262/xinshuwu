<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/15
 * Time: 17:19
 */

namespace App\Events\Order;

use App\Models\AfterSaleOrder;

class AfterSaleWasUpdated
{
    public $order;

    public function __construct(AfterSaleOrder $order)
    {
        $this->order = $order;
    }
}
