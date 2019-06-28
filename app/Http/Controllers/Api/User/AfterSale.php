<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/14
 * Time: 12:41
 */

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\AfterSaleOrder;
use App\Models\OrderGoods;
use App\Repos\User\AfterSaleRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Order;
use \App\Models\User;

class AfterSale extends Controller
{
    /**
     * 售后列表
     * @param Request $request
     * @param AfterSaleRepo $afterSaleRepo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request, AfterSaleRepo $afterSaleRepo)
    {
        /** @var User $merchant */
        $user = $request->user();
        $this->validate($request, [
            'status'     => 'array',
            'type'       => [
                'string',
                Rule::in([
                    AfterSaleOrder::T_RETURN_REFUND,
                    AfterSaleOrder::T_REFUND
                ])
            ],
            'time_start' => 'string|date',
            'time_end'   => 'string|date',
        ]);

        return $afterSaleRepo->list($user, $request->only([
            'time_start',
            'time_end',
            'type',
            'status',
            'goods_name',
        ]));
    }

    /**
     * 申请退款
     * @param Request $request
     * @param AfterSaleRepo $afterSaleRepo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function create(Request $request, AfterSaleRepo $afterSaleRepo)
    {
        /** @var $user */
        $user = $request->user();
        $this->validate($request, [
            'order_goods_id' => [
                'required',
                'string',
                Rule::exists('xsw_user_order_goods', 'id')
                    ->where('user_id', $user->id)
            ],
            'reason'         => [
                'required',
                'string',
                Rule::in(array_keys(AfterSaleOrder::REASON_MAPPING)),
            ],
            'type'           => [
                'required',
                'string',
                Rule::in(array_keys(AfterSaleOrder::TYPE_MAPPING)),
            ],
            'refund_amount'  => 'required|numeric|min:0.01',
            'images'         => 'array'
        ]);

        /** @var OrderGoods $orderGoods */
        $orderGoods = OrderGoods::query()->find($request->input('order_goods_id'));
        $afterSale  = $orderGoods->aftersale;
        $order      = $orderGoods->order;

        $orderExc        = $order->status === Order::S_CREATED;
        $afterSaleExc    = $afterSale &&
            !in_array($afterSale->status, [AfterSaleOrder::S_CANCEL, AfterSaleOrder::S_REJECTED]);
        $refundAmountExc = $request->input('refund_amount') > $orderGoods->pay_amount;


        if ($orderExc || $afterSaleExc || $refundAmountExc) {
            return json_response([], '操作异常~~', 406);
        }

        return $afterSaleRepo->create($user, $request->only([
            'order_goods_id',
            'type',
            'reason',
            'refund_amount',
            'note',
            'images',
        ]));
    }

    /**
     * 寄回商品
     * @param Request $request
     * @param AfterSaleRepo $afterSaleRepo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function dispatchGoods(Request $request, AfterSaleRepo $afterSaleRepo)
    {
        /** @var User $user */
        $user = $request->user();

        $this->validate($request, [
            'aftersale_no'     => [
                'required',
                'string',
                Rule::exists('xsw_aftersale_order', 'aftersale_no')
                    ->where('status', AfterSaleOrder::S_AGREED)
                    ->where('user_id', $user->id)
            ],
            'logistic_no'      => 'required|string|min:5',
            'company_id'       => 'required|int|exists:xsw_express_company,id',
            'shipping_address' => 'required|string',
        ], [
            'logistic_no.required'      => '物流单号不能为空~~',
            'logistic_no.min'           => '物流单号非法~~',
            'aftersale_no.exists'       => '订单不存在或者已发货',
            'company_id.exists'         => '物流公司不存在哦',
            'shipping_address.required' => '寄件人地址不能为空~~',
        ]);

        return $afterSaleRepo->dispatchGoods($user, $request->only([
            'aftersale_no',
            'logistic_no',
            'company_id',
            'shipping_address',
        ]));
    }

    /**
     * 取消退款申请
     * @param Request $request
     * @param AfterSaleRepo $afterSaleRepo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function cancel(Request $request, AfterSaleRepo $afterSaleRepo)
    {
        /** @var User $user */
        $user = $request->user();

        $this->validate($request, [
            'aftersale_no' => [
                'required',
                'string',
                Rule::exists('xsw_aftersale_order', 'aftersale_no')
                    ->where('status', AfterSaleOrder::S_REQUEST)
                    ->where('user_id', $user->id)
            ],
        ]);

        return $afterSaleRepo->cancel($user, $request->only([
            'aftersale_no'
        ]));
    }

    /**
     * 退款详情
     * @param Request $request
     * @param AfterSaleRepo $afterSaleRepo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function info(Request $request, AfterSaleRepo $afterSaleRepo)
    {
        /** @var User $user */
        $user = $request->user();

        $this->validate($request, [
            'aftersale_no' => [
                'required',
                'string',
                Rule::exists('xsw_aftersale_order', 'aftersale_no')
                    ->where('user_id', $user->id)
            ],
        ]);

        return $afterSaleRepo->info($user, $request->only([
            'aftersale_no'
        ]));
    }

    public function reasons(AfterSaleRepo $afterSaleRepo)
    {
        return $afterSaleRepo->reasons();
    }

    /**
     * 退款详情
     * @param Request $request
     * @param AfterSaleRepo $afterSaleRepo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function status(Request $request, AfterSaleRepo $afterSaleRepo)
    {
        /** @var User $user */
        $user = $request->user();

        $this->validate($request, [
            'aftersale_no' => [
                'required',
                'string',
                Rule::exists('xsw_aftersale_order', 'aftersale_no')
                    ->where('user_id', $user->id)
            ],
        ]);

        return $afterSaleRepo->status($user, $request->only([
            'aftersale_no'
        ]));
    }
}
