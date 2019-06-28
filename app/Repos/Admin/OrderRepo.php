<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 18/04/2019
 * Time: 21:59
 */

namespace App\Repos\Admin;

use App\Events\Order\OrderWasUpdated;
use App\Models\ExpressCompany;
use App\Models\MerchantAccount;
use App\Models\Order;
use App\Models\OrderGoods;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;

class OrderRepo
{
    /**
     * @param MerchantAccount $merchantAccount
     * @param array           $query
     *
     * @return mixed
     */
    public function list($query = [], $offset = 0, $pageSize = 0)
    {
        $builder = Order::query();

        if ($orderNo = array_get($query, 'order_no')) {
            $builder->where('order_no', $orderNo);
        }

        if ($begin = array_get($query, 'begin')) {
            $builder->where('created_at', '>=', $begin);
        }

        if ($end = array_get($query, 'end')) {
            $builder->where('created_at', '<=', $end);
        }

        if ($method = array_get($query, 'logistic_abbr')) {
            $builder->where('logistic_abbr', $method);
        }

        if ($method = array_get($query, 'pay_method')) {
            $builder->where('pay_method', $method);
        }

        if ($status = array_get($query, 'status')) {
            $builder->where('status', $status);
        }
        if ($user_id = array_get($query, 'user_id')) {
            $builder->where('user_id', $user_id);
        }

        if ($goodsName = array_get($query, 'goods_name')) {
            // hack method
            $builder->whereHas('goods', function ($q) use ($goodsName) {
                /** @var Builder $q */
                $q->whereRaw(
                    \DB::raw("JSON_EXTRACT(`snapshot`, \"$.goods_info.name\") LIKE '%" . $goodsName . "%'")
                );
            });
        }

        if ($userName = array_get($query, 'user_name')) {
            $builder->when($userName, function ($query) use ($userName) {
                return $query->whereHas('user', function ($query) use ($userName) {
                    return $query->where('nickname', 'like', '%'.$userName.'%');
                });
            });
        }

        $Total = $builder->count();

        //暂时考虑兼容性
        if(!empty($pageSize)) {
            $builder->offset($offset)->limit($pageSize);
        }

        $orders = $builder->select([
            'id',
            'batch_no',
            'order_no',
            'store_id',
            'total_amount',
            'pay_amount',
            'goods_price',
            'delivery_price',
            'pay_method',
            'address',
            'status',
            'logistic_no',
            'logistic_company',
            'logistic_info',
            'created_at',
            'updated_at',
            'is_test'
        ])->with([
            'goods',
        ])->orderBy('id', 'desc')
            ->get();

        $orders_return = $orders->map(function ($o) {
            /** @var Order $o */

            $goods = $o->goods->map(function ($g) {
                /** @var OrderGoods $g */
                return $g->goods_info;
            });

            $o = $o->toArray();

            $o['goods'] = $goods;

            return $o;
        });
        //暂时考虑兼容性
        if(empty($pageSize)) {
            return $orders_return;
        }
        return ['total'=>$Total,'list'=>$orders_return];

    }
    
    /**
     * @param $oderNo
     * @param $logisticNo
     * @param $companyId
     */
    public function dispatchGoods($oderNo, $logisticNo, $companyId)
    {
        /** @var Order $order */
        $order = Order::query()
            ->where('order_no', $oderNo)
            ->first();
        
        /** @var ExpressCompany $company */
        $company = ExpressCompany::query()->find($companyId);
        
        $order->update([
            'status'           => Order::S_SHIPPED,
            'logistic_no'      => $logisticNo,
            'logistic_company' => $company->name,
            'logistic_abbr'    => $company->abbr,
        ]);
        
        event(new OrderWasUpdated($order));
    }
    
    /**
     * @param $oderNo
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function info($oderNo)
    {
        /** @var Order $order */
        $order = Order::query()->with('goods')->where('order_no', $oderNo)->first([
            'batch_no',
            'order_no',
            'store_id',
            'total_amount',
            'pay_amount',
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
            'is_test'
        ]);
        
        $goods = $order->goods->map(function ($g) {
            /** @var OrderGoods $g */
            return $g->goods_info;
        });
        
        $order = $order->toArray();
        
        $order['goods'] = $goods;
        $order['status_str'] = Order::STATUS_MAPPING[$order['status']];
        
        return $order;
    }

    /**
     * @param                 $id
     * @param                 $data
     */
    public function update($id, $data)
    {
        /** @var Article $article */
        $order = Order::query()->find($id);

        $order->update($data);

    }
}
