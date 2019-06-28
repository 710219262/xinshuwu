<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 14/04/2019
 * Time: 14:56
 */

namespace App\Repos\User;

use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\User;

class GoodsRepo
{
    /**
     * @param User $user
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getBoughtGoods(User $user)
    {
        $orderNos = $user->orderRlt()
            ->whereIn('status', [Order::S_RECEIVED, Order::S_COMPLETED, Order::S_SHARED])
            ->get()
            ->pluck('order_no');
        
        $orderGoods = OrderGoods::query()
            ->whereIn('order_no', $orderNos)
            ->orderBy('id', 'DESC')
            ->get();
        
        
        $orderGoods = $orderGoods->map(function ($o) {
            /** @var OrderGoods $o */
            return $o->goods_info;
        });
        
        return $orderGoods;
    }
}
