<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 18/04/2019
 * Time: 21:59
 */

namespace App\Repos\Merchant;

use App\Events\Order\OrderWasUpdated;
use App\Models\AfterSaleOrder;
use App\Models\ExpressCompany;
use App\Models\MerchantAccount;
use App\Models\Order;
use App\Models\OrderGoods;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

class OrderRepo
{
    /**
     * @param MerchantAccount $merchantAccount
     * @param array           $query
     *
     * @return mixed
     */
    public function list(MerchantAccount $merchantAccount, $query = [], $offset = 0, $pageSize = 0)
    {
        $orders = $merchantAccount->orders($query, $offset, $pageSize);
        //暂时考虑兼容性
        if(empty($pageSize)) {
            return $orders->map(function ($o) {
                /** @var Order $o */

                $goods = $o->goods->map(function ($g) {
                    /** @var OrderGoods $g */
                    return $g->goods_info;
                });

                $o = $o->toArray();

                $o['goods'] = $goods;

                return $o;
            });
        }else{
            $orderslist = $orders['list']->map(function ($o) {
                /** @var Order $o */

                $goods = $o->goods->map(function ($g) {
                    /** @var OrderGoods $g */
                    return $g->goods_info;
                });

                $o = $o->toArray();

                $o['goods'] = $goods;

                return $o;
            });
            return ['total'=>$orders['total'],'list'=>$orderslist];
        }
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
        $goods_tem = array();
        foreach ($goods as $key=>$val){
            $info = AfterSaleOrder::query()->where('order_goods_id', $val['id'])->first();
            if (!empty($info)) {
                $val['aftersale_status'] = $info->status;
            } else {
                $val['aftersale_status'] = '';
            }
            array_push($goods_tem,$val);
        }

        $order = $order->toArray();

        $order['goods'] = $goods_tem;
        $order['status_str'] = Order::STATUS_MAPPING[$order['status']];

        return $order;
    }

    public function queryList($data)
    {
        $query = \DB::table('xsw_user_order_goods as og');
        $this->buildQuery($query, $data);

        $orders = $query->leftJoin('xsw_user_order as o', 'o.order_no', '=', 'og.order_no')
            ->leftJoin('xsw_user as u', 'u.id', '=', 'og.user_id')
            ->leftJoin('xsw_merchant_info as m', 'm.account_id', '=', 'o.store_id')
            ->select([
                'og.count',
                'og.total_amount',
                'og.pay_amount',
                'og.org_per_price',
                'og.pay_per_price',
                'og.goods_id',
                'og.snapshot',
                'o.status',
                'o.created_at',
                'u.nickname as user_nickname',
                'm.company_name'
            ])->get();
        $orders = $orders->map(function ($order) {
            $order->total_amount = floatval($order->total_amount);
            $order->pay_amount = floatval($order->pay_amount);
            $order->org_per_price = floatval($order->org_per_price);
            $order->pay_per_price = floatval($order->pay_per_price);

            $snapshot = json_decode($order->snapshot, true);
            $order->goods_name = $snapshot['goods_info']['name'];
            $order->goods_banner_imgs = $snapshot['goods_info']['banner_imgs'];
            $order->goods_price = $snapshot['sku']['price'];
            unset($order->snapshot);
            return $order;
        });

        return json_response($orders);
    }

    /**
     *
     * @param MerchantAccount $merchant
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function statistics(MerchantAccount $merchant)
    {
        $orders = Order::query()
            ->where('store_id', $merchant->id)
            ->sharedLock()
            ->select(
                \DB::raw("IFNULL(SUM(if(status<>'CANCELED', total_amount, 0)),0) as amount"),
                \DB::raw("sum(if(status='PAYED', 1, 0)) as order_count_created"),
                \DB::raw("sum(if(status<>'CANCELED', 1, 0)) as order_count")
            )
            ->first();

        return json_response([
            'amount'   => array_get($orders, 'amount', 0),
            'order_count_created'   => array_get($orders, 'order_count_created', 0),
            'order_count'   => array_get($orders, 'order_count', 0)
        ]);
    }

    /**
     *
     * @param MerchantAccount $merchant
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function statisticsToday(MerchantAccount $merchant)
    {
        $orders = Order::query()
            ->where('store_id', $merchant->id)
            ->where('created_at', '>', Carbon::today())
            ->sharedLock()
            ->select(
                \DB::raw("IFNULL(SUM(if(status<>'CANCELED', total_amount, 0)),0) as amount"),
                \DB::raw("sum(if(status='PAYED', 1, 0)) as order_count_created"),
                \DB::raw("sum(if(status<>'CANCELED', 1, 0)) as order_count")
            )
            ->first();

        return json_response([
            'amount'   => array_get($orders, 'amount', 0),
            'order_count_created'   => array_get($orders, 'order_count_created', 0),
            'order_count'   => array_get($orders, 'order_count', 0)
        ]);
    }
    /**
     * @param Builder $query
     * @param $data
     */
    protected function buildQuery(Builder $query, $data)
    {
        if (!empty(array_get($data, 'store_id'))) {
            $query->where('o.store_id', $data['store_id']);
        }

        if (!empty(array_get($data, 'order_no'))) {
            $query->where('o.order_no', $data['order_no']);
        }

        if (!empty(array_get($data, 'status'))) {
            $query->whereIn('o.status', $data['status']);
        }

        if (!empty(array_get($data, 'logistic_no'))) {
            $query->where('o.logistic_no', $data['logistic_no']);
        }

        if (!empty(array_get($data, 'time_start'))) {
            $query->where('o.created_at', '>', $data['time_start']);
        }

        if (!empty(array_get($data, 'time_end'))) {
            $query->where('o.created_at', '<', $data['time_end']);
        }

        if (!empty(array_get($data, 'time'))) {
            $query->where('o.created_at', '>', Carbon::today()->addMonth(-1));
        }

        if (!empty(array_get($data, 'goods_name'))) {
            $query->where('og.snapshot->goods_info->name', 'LIKE', "%{$data['goods_name']}%");
        }

        if (!empty(array_get($data, 'store_name'))) {
            $query->where('m.company_name', 'LIKE', "%{$data['store_name']}%");
        }
        if (!empty(array_get($data, 'user_name'))) {
            $query->where('u.nickname', 'LIKE', "%{$data['user_name']}%");
        }
        if (!empty(array_get($data, 'pay_method'))) {
            $query->where('o.pay_method', $data['pay_method']);
        }
    }
}
