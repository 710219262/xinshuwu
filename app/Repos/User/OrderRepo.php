<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 13/04/2019
 * Time: 23:39
 */

namespace App\Repos\User;

use App\Models\AfterSaleOrder;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;

class OrderRepo
{
    public function list(User $user, $status)
    {
        $orders = $user->orders($status);
        
        return $orders->map(function ($o) {
            /** @var Order $o */
            $afterSaleStatus = Order::S_CANCELED;
            
            $goods = $o->goods->map(function ($g) use ($o, &$afterSaleStatus) {
                if (($g->aftersale && $g->aftersale->status !== AfterSaleOrder::S_COMPLETED) ||
                    !$g->aftersale
                ) {
                    $afterSaleStatus = $o->status;
                }
                /** @var OrderGoods $g */
                return array_merge($g->goods_info, [
                    'aftersale' => $g->aftersale,
                ]);
            });
            
            $o = $o->toArray();
            
            $o['goods'] = $goods;
            $o['status'] = $afterSaleStatus;
            
            return $o;
        });
    }
    
    /**
 * @param $oderNo
 *
 * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
 */
    public function info($oderNo)
    {
        /** @var Order $order */
        $order = Order::query()->with([
            'goods' => function (HasMany $q) {
                $q->with([
                    'aftersale' => function (HasOne $q) {
                        $q->select([
                            'aftersale_no',
                            'order_goods_id',
                            'status',
                        ]);
                    },
                ]);
            },
            'store' => function ($q) {
                /** @var Builder $q */
                $q->select(
                    'id',
                    'name',
                    'phone'
                );
            },
        ])->where('order_no', $oderNo)->first([
            'batch_no',
            'order_no',
            'store_id',
            'total_amount',
            'pay_amount',
            \DB::raw("(total_amount - pay_amount) as discount_amount"),
            'goods_price',
            'delivery_price',
            'pay_method',
            'address',
            'status',
            'logistic_no',
            'logistic_company',
            'logistic_info',
            'logistic_abbr',
            'created_at',
            'updated_at',
        ]);

        $afterSaleStatus = Order::S_CANCELED;

        $goods = $order->goods->map(function ($g) use ($order, &$afterSaleStatus) {
            if (($g->aftersale && $g->aftersale->status !== AfterSaleOrder::S_COMPLETED) ||
                !$g->aftersale
            ) {
                $afterSaleStatus = $order->status;
            }
            /** @var OrderGoods $g */
            return array_merge($g->goods_info, [
                'aftersale' => $g->aftersale,
            ]);
        });

        $order = $order->toArray();

        $order['goods'] = $goods;
        $order['status'] = $afterSaleStatus;
        $order['status_str'] = Order::STATUS_MAPPING[$order['status']];

        return $order;
    }

    /**
     * @param $ordergoodsid
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function goodsinfo($ordergoodsid)
    {
        /** @var Order $order */
        //$ordergoods = OrderGoods::query()->where('id', $ordergoodsid)->first();
        $ordergoods =  OrderGoods::query()->find($ordergoodsid);
        return $ordergoods;
    }
}
