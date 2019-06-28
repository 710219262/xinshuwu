<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/16
 * Time: 10:22
 */

namespace App\Repos\Merchant;

use App\Events\Order\AfterSaleWasUpdated;
use App\Models\AfterSaleOrder;
use App\Models\MerchantAccount;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

class AfterSaleRepo
{
    public function list($data)
    {
        $query = \DB::table('xsw_aftersale_order as ao');
        $this->buildQuery($query, $data);

        $orders = $query->leftJoin('xsw_user_order_goods as og', 'og.id', '=', 'ao.order_goods_id')
            ->leftJoin('xsw_user_order as o', 'o.order_no', '=', 'ao.order_no')
            ->leftJoin('xsw_user as u', 'u.id', '=', 'ao.user_id')
            ->leftJoin('xsw_merchant_info as m', 'm.account_id', '=', 'ao.store_id')
            ->leftJoin('xsw_merchant_account as a', 'a.id', '=', 'ao.store_id')
            ->select([
                'ao.*',
                'og.count',
                'og.total_amount',
                'og.pay_amount',
                'og.org_per_price',
                'og.pay_per_price',
                'og.goods_id',
                'og.snapshot',
                'u.nickname as user_nickname',
                'm.company_name',
                'a.name as store_name',
            ])->get();

        $orders = $orders->map(function ($order) {
            $order->total_amount  = floatval($order->total_amount);
            $order->pay_amount    = floatval($order->pay_amount);
            $order->org_per_price = floatval($order->org_per_price);
            $order->pay_per_price = floatval($order->pay_per_price);

            $order->images = json_decode($order->images);
            $snapshot      = json_decode($order->snapshot, true);
            $order->reason = array_get(AfterSaleOrder::REASON_MAPPING, $order->reason, $order->reason);

            $order->goods_name        = $snapshot['goods_info']['name'];
            $order->goods_banner_imgs = $snapshot['goods_info']['banner_imgs'];
            $order->goods_price       = $snapshot['sku']['price'];
            unset($order->snapshot);
            return $order;
        });

        return json_response($orders);
    }

    public function audit($data)
    {
        /** @var AfterSaleOrder $order */
        $order = AfterSaleOrder::query()
            ->where('store_id', $data['store_id'])
            ->where('aftersale_no', $data['aftersale_no'])
            ->first();

        /** @var MerchantAccount $merchant */
        $merchant = MerchantAccount::query()->find($data['store_id']);

        $order && $order->update([
            'status'          => $data['status'],
            'merchant_note'   => $data['merchant_note'],
            'receive_address' => $merchant->merchantInfo->consignee_addr,
            'receive_name'    => $merchant->merchantInfo->contact,
            'receive_phone'   => $merchant->merchantInfo->phone,
            'audit_at'        => Carbon::now(),
        ]);

        event(new AfterSaleWasUpdated($order));
        return json_response();
    }

    public function receive($data)
    {
        /** @var AfterSaleOrder $order */
        $order = AfterSaleOrder::query()
            ->where('store_id', $data['store_id'])
            ->where('aftersale_no', $data['aftersale_no'])
            ->first();

        $order->update([
            'status'     => AfterSaleOrder::S_RECEIVED,
            'receive_at' => Carbon::now(),
        ]);

        event(new AfterSaleWasUpdated($order));
        return json_response();
    }

    /**
     * @param Builder $query
     * @param $data
     */
    protected function buildQuery(Builder $query, $data)
    {
        if (!empty(array_get($data, 'store_id'))) {
            $query->where('ao.store_id', $data['store_id']);
        }

        if (!empty(array_get($data, 'order_no'))) {
            $query->where('o.order_no', $data['order_no']);
        }

        if (!empty(array_get($data, 'aftersale_no'))) {
            $query->where('ao.aftersale_no', $data['aftersale_no']);
        }

        if (!empty(array_get($data, 'type'))) {
            $query->where('ao.type', $data['type']);
        }

        if (!empty(array_get($data, 'status'))) {
            $query->whereIn('ao.status', $data['status']);
        }

        if (!empty(array_get($data, 'logistic_no'))) {
            $query->where('ao.logistic_no', $data['logistic_no']);
        }

        if (!empty(array_get($data, 'time_start'))) {
            $query->where('ao.created_at', '>', $data['time_start']);
        }

        if (!empty(array_get($data, 'time_end'))) {
            $query->where('ao.created_at', '<=', $data['time_end']);
        }

        if (!empty(array_get($data, 'time'))) {
            $query->where('ao.created_at', '>', Carbon::today()->addMonth(-1));
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
