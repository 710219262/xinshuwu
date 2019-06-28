<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/14
 * Time: 12:58
 */

namespace App\Repos\User;

use App\Models\AfterSaleOrder;
use App\Models\ExpressCompany;
use App\Models\OrderGoods;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;

class AfterSaleRepo
{

    public function list(User $user, $data)
    {
        $query = \DB::table('xsw_aftersale_order as ao')
            ->where('ao.user_id', $user->id)
            ->orderBy('ao.id', 'DESC');
        $this->buildQuery($query, $data);

        $orders = $query->leftJoin('xsw_user_order_goods as og', 'og.id', '=', 'ao.order_goods_id')
            ->leftJoin('xsw_merchant_info as m', 'm.account_id', '=', 'ao.store_id')
            ->leftJoin('xsw_merchant_account as s', 's.id', 'ao.store_id')
            ->select([
                'ao.aftersale_no',
                'ao.reason',
                'ao.user_note',
                'ao.merchant_note',
                'ao.images',
                'ao.refund_amount',
                'ao.status',
                'ao.created_at',
                'ao.audit_at',
                'ao.receive_at',
                'ao.refund_at',
                'ao.cancel_at',
                'ao.dispatch_at',
                'og.count',
                'og.total_amount',
                'og.pay_amount',
                'og.org_per_price',
                'og.pay_per_price',
                'og.goods_id',
                'og.snapshot',
                's.logo as store_logo',
                's.id as store_id',
                's.name as store_name',
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
            $order->goods_sku_name       = $snapshot['sku']['sku_name'];
            $order->goods_has_spec       = $snapshot['sku']['has_spec'];
            unset($order->snapshot);
            return $order;
        });

        return json_response($orders);
    }

    /**
     * @param User $user
     * @param $data
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function create(User $user, $data)
    {
        /** @var OrderGoods $orderGoods */
        $orderGoods = OrderGoods::query()->find($data['order_goods_id']);
        $order      = AfterSaleOrder::query()
            ->create([
                'aftersale_no'   => AfterSaleOrder::newOrderNum($user->phone),
                'order_no'       => $orderGoods->order_no,
                'user_id'        => $orderGoods->user_id,
                'store_id'       => $orderGoods->order->store_id,
                'order_goods_id' => $data['order_goods_id'],
                'type'           => $data['type'],
                'refund_amount'  => $data['refund_amount'].'',
                'reason'         => $data['reason'],
                'user_note'      => array_get($data, 'note', ''),
                'images'         => array_get($data, 'images', [])
            ]);
        return json_response($order);
    }

    /**
     * @param User $user
     * @param $data
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function dispatchGoods(User $user, $data)
    {
        /** @var AfterSaleOrder $order */
        $order = AfterSaleOrder::query()
            ->where('aftersale_no', $data['aftersale_no'])
            ->where('user_id', $user->id)
            ->first();

        /** @var ExpressCompany $company */
        $company = ExpressCompany::query()->find($data['company_id']);

        $order->update([
            'status'           => AfterSaleOrder::S_SHIPPING,
            'logistic_no'      => $data['logistic_no'],
            'logistic_company' => $company->name,
            'logistic_abbr'    => $company->abbr,
            'shipping_address' => $data['shipping_address'],
            'shipping_name'    => array_get($data, 'shipping_name', $user->nickname),
            'shipping_phone'   => array_get($data, 'shipping_phone', $user->phone),
            'dispatch_at'      => Carbon::now(),
        ]);
        return json_response();
    }

    /**
     * @param User $user
     * @param $data
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function cancel(User $user, $data)
    {
        AfterSaleOrder::query()
            ->where('user_id', $user->id)
            ->where('aftersale_no', $data['aftersale_no'])
            ->update([
                'status'    => AfterSaleOrder::S_CANCEL,
                'cancel_at' => Carbon::now(),
            ]);
        return json_response();
    }

    /**
     * @param User $user
     * @param $data
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function info(User $user, $data)
    {
        $order          = AfterSaleOrder::query()
            ->where('user_id', $user->id)
            ->where('aftersale_no', $data['aftersale_no'])
            ->with([
                'orderGoods' => function (HasOne $q) {
                    $q->select([
                        'id',
                        'goods_id',
                        'snapshot',
                        'pay_amount',
                        'total_amount',
                        'org_per_price',
                        'pay_per_price',
                        'count',
                        'created_at',
                    ]);
                }
            ])
            ->select([
                'order_goods_id',
                'reason',
                'user_note',
                'merchant_note',
                'images',
                'refund_amount',
                'type',
                'status',
                'receive_name',
                'receive_phone',
                'receive_address',
                'created_at',
                'audit_at',
                'receive_at',
                'refund_at',
                'cancel_at',
                'dispatch_at',
            ])
            ->first();
        $orderGoodsInfo = $order->orderGoods->goods_info;
        $order->refund_amount = floatval($order->refund_amount);
        $order->receive_name = $order->receive_name ?: '';
        $order->receive_phone = $order->receive_phone ?: '';
        $order->receive_address = $order->receive_address ?: '';
        $order          = $order->toArray();
        unset($order['order_goods']);
        $order['goods'] = $orderGoodsInfo;
        return json_response($order);
    }

    public function reasons()
    {
        $reasons = [];
        foreach (AfterSaleOrder::REASON_MAPPING as $key => $val) {
            $reasons[] = [
                'key' => $key,
                'val' => $val
            ];
        }
        return json_response($reasons);
    }

    /**
     * @param Builder $query
     * @param $data
     */
    protected function buildQuery(Builder $query, $data)
    {
        if (!empty(array_get($data, 'type'))) {
            $query->where('ao.type', $data['type']);
        }

        if (!empty(array_get($data, 'status'))) {
            $query->whereIn('ao.status', $data['status']);
        }

        if (!empty(array_get($data, 'time_start'))) {
            $query->where('ao.created_at', '>', $data['time_start']);
        }

        if (!empty(array_get($data, 'time_end'))) {
            $query->where('ao.created_at', '<=', $data['time_end']);
        }

        if (!empty(array_get($data, 'goods_name'))) {
            $query->where('og.snapshot->goods_info->name', 'LIKE', "%{$data['goods_name']}%");
        }
    }

    /**
     * @param User $user
     * @param $data
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function status(User $user, $data)
    {
        $order          = AfterSaleOrder::query()
            ->where('user_id', $user->id)
            ->where('aftersale_no', $data['aftersale_no'])
            ->select([
                'order_goods_id',
                'reason',
                'user_note',
                'merchant_note',
                'images',
                'refund_amount',
                'type',
                'status',
                'receive_name',
                'receive_phone',
                'receive_address',
                'created_at',
                'audit_at',
                'receive_at',
                'refund_at',
                'cancel_at',
                'dispatch_at',
            ])
            ->first();
        $res = array();
        $res['order_goods_id'] = $order['order_goods_id'];
        $res['refund_amount'] = $order['refund_amount'];
        $res['type'] = $order['type'];
        $res['status_name'] = $order['type'] == 'REFUND'? '退款' : '退货退款';
        $res['status'] = array();
        $str = '提交退款申请';
        if($order['type'] == 'REFUND') {
            if (!empty($order['created_at'])) array_push($res['status'], array('msg' => '提交退款申请', 'msg_date' => $order['created_at']->format('Y-m-d H:i:s')));
            if (!empty($order['audit_at'])) {
                if ($order['status'] == 'REJECTED') {
                    $str = '审核未通过';
                    array_push($res['status'], array('msg' => '审核未通过：' . $order['merchant_note'], 'msg_date' => $order['audit_at']));
                } else {
                    $str = '审核通过';
                    array_push($res['status'], array('msg' => '审核通过：' . $order['merchant_note'], 'msg_date' => $order['audit_at']));
                }
            }
            if (!empty($order['refund_at'])){
                $str = '退款完成';
                array_push($res['status'], array('msg' => '退款完成', 'msg_date' => $order['refund_at']));
            }
        }
        else {
            if (!empty($order['created_at'])) array_push($res['status'], array('msg' => '提交退款申请', 'msg_date' => $order['created_at']->format('Y-m-d H:i:s')));
            if (!empty($order['audit_at'])) {
                if ($order['status'] == 'REJECTED') {
                    $str = '审核未通过';
                    array_push($res['status'], array('msg' => '审核未通过：' . $order['merchant_note'], 'msg_date' => $order['audit_at']));
                } else {
                    $str = '审核通过';
                    array_push($res['status'], array('msg' => '审核通过：' . $order['merchant_note'], 'msg_date' => $order['audit_at']));
                }
            }
            if (!empty($order['dispatch_at'])){
                $str = '寄回商品';
                array_push($res['status'], array('msg' => '寄回商品，提交物流公司及物流单号', 'msg_date' => $order['dispatch_at']));
            }
            if (!empty($order['receive_at'])){
                $str = '商家处理';
                array_push($res['status'], array('msg' => '商家处理，完成退货退款', 'msg_date' => $order['receive_at']));
            }
            if (!empty($order['refund_at'])){
                $str = '退款完成';
                array_push($res['status'], array('msg' => '退款完成', 'msg_date' => $order['refund_at']));
            }
        }
        $res['status_name'] = $res['status_name'] . '(' . $str . ')';
        return json_response($res);
    }
}
