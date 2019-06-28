<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/8
 * Time: 9:57
 */

namespace App\Events\Order;

use App\Models\UserVipOrder;

class VipOrderWasPaid
{
    public $order;

    public function __construct(UserVipOrder $order)
    {
        $this->order = $order;
    }
}
