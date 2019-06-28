<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 25/03/2019
 * Time: 13:11
 */

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Logics\Order\OrderLogic;
use App\Models\Order as OrderModel;
use App\Models\User;
use App\Repos\Redis\UserCacheRepo;
use App\Repos\User\OrderRepo;
use App\Services\LogisticInfoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Order extends Controller
{
    /**
     * @param Request    $request
     *
     * @param OrderLogic $orderLogic
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function createViaCart(Request $request, OrderLogic $orderLogic)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'cart_ids'   => 'required|array',
            'cart_ids.*' => [
                'required',
                'int',
                Rule::exists('xsw_user_shopping_cart', 'id')
                    ->whereNull('deleted_at')
                    ->where('user_id', $user->id),
            ],
            'address_id' => [
                'required',
                'int',
                Rule::exists('xsw_user_address', 'id')
                    ->where('user_id', $user->id),
            ],
        ], [
            'cart_ids.*.exists' => '购物车的商品不翼而飞了~',
        ]);
        
        return $orderLogic->createOrderViaCart($user, $request->only(['cart_ids', 'address_id']));
    }
    
    /**
     * @param Request    $request
     * @param OrderLogic $orderLogic
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function createInstant(Request $request, OrderLogic $orderLogic)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'goods_id'   => [
                'required',
                'int',
                Rule::exists('xsw_goods_info', 'id')
                //todo status should verify too
            ],
            'sku_id'     => [
                'required',
                'int',
                Rule::exists('xsw_goods_sku', 'id')
                    ->where('goods_id', $request->input('goods_id'))
                    ->whereNull('deleted_at'),
            ],
            'count'      => 'required|int|min:1',
            'address_id' => [
                'required',
                'int',
                Rule::exists('xsw_user_address', 'id')
                    ->where('user_id', $user->id),
            ],
        ], [
            'goods_id.exists' => '商品不翼而飞了~',
            'sku_id.exists'   => '该型号不翼而飞了~',
        ]);
        
        return $orderLogic->createOrderInstant($user, $request->only([
            'goods_id',
            'sku_id',
            'count',
            'address_id',
        ]));
    }
    
    /**
     * @param Request   $request
     * @param OrderRepo $orderRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request, OrderRepo $orderRepo)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'status' => [Rule::in(array_keys(OrderModel::STATUS_MAPPING))],
        ]);
        
        $orderList = $orderRepo->list($user, $request->input('status'));
        
        return json_response($orderList);
    }
    
    /**
     * @param Request   $request
     * @param OrderRepo $orderRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function info(Request $request, OrderRepo $orderRepo)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'order_no' => [
                'required',
                'string',
                Rule::exists('xsw_user_order')
                    ->where('user_id', $user->id),
            ],
        ], [
            'order_no.exists' => '订单号不存在哦',
        ]);
        
        return json_response($orderRepo->info($request->input('order_no')));
    }
    
    /**
     * @param Request    $request
     *
     * @param OrderLogic $orderLogic
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function prepayViaAli(Request $request, OrderLogic $orderLogic)
    {
        return $this->prepay($request, OrderModel::P_ALI, $orderLogic);
    }
    
    /**
     * @param Request    $request
     *
     * @param OrderLogic $orderLogic
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function prepayViaWechat(Request $request, OrderLogic $orderLogic)
    {
        return $this->prepay($request, OrderModel::P_WECHAT, $orderLogic);
    }

    /**
     * @param Request    $request
     *
     * @param OrderLogic $orderLogic
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function prepayViaWechatJsapi(Request $request, OrderLogic $orderLogic)
    {
        return $this->prepay($request, OrderModel::P_WECHAT_JSAPI, $orderLogic);
    }
    /**
     * @param            $request
     * @param            $pay
     * @param OrderLogic $orderLogic
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function prepay(Request $request, $pay, OrderLogic $orderLogic)
    {
        /** @var User $user */
        $user = $request->user();
        
        /** @var bool 是多订单支付 $isBatch */
        $isBatch = false;
        if ($request->has('order_no')) {
            $this->validate($request, [
                'order_no' => [
                    'required',
                    'string',
                    Rule::exists('xsw_user_order', 'order_no')
                        ->where('user_id', $user->id)
                        ->where('status', OrderModel::S_CREATED),
                ],
            ], [
                'order_no.exists' => '订单不翼而飞了哦~',
            ]);
            $orderNO = $request->input('order_no');
        } else {
            $this->validate($request, [
                'batch_no' => [
                    'required',
                    'string',
                    Rule::exists('xsw_user_order', 'batch_no')
                        ->where('user_id', $user->id)
                        ->where('status', OrderModel::S_CREATED),
                ],
            ], [
                'batch_no.exists' => '订单不翼而飞了哦~',
            ]);
            $orderNO = $request->input('batch_no');
            $isBatch = true;
        }
        if (OrderModel::P_WECHAT_JSAPI === $pay) {
            $this->validate($request, [
                'openid' => [
                    'required',
                    'string',
                ],
            ]);
            $openid = $request->input('openid');
        }
        //ensure batch no has multi order
        if ($isBatch && OrderModel::query()->where('batch_no', $orderNO)->count() === 1) {
            $isBatch = false;
            $orderNO = OrderModel::query()->where('batch_no', $orderNO)->first()->order_no;
        }

        if (OrderModel::P_WECHAT_JSAPI === $pay) {
            return $orderLogic->prepayViaWechatJsapi($orderNO, $isBatch, $openid);
        } else if (OrderModel::P_WECHAT === $pay) {
            return $orderLogic->prepayViaWechat($orderNO, $isBatch);
        } else {
            return $orderLogic->prepayViaAli($orderNO, $isBatch);
        }
    }
    
    /**
     * @param Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function refund(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'order_no' => [
                'required',
                'string',
                Rule::exists('xsw_user_order', 'order_no')
                    ->where('user_id', $user->id)
                    ->where('status', OrderModel::S_CREATED),
            ],
            'reason'   => [
                'required',
                'string',
                Rule::in(array_keys(OrderModel::REFUND_REASON_MAPPING)),
            ],
            'type'     => [
                'required',
                'string',
                Rule::in(array_keys(OrderModel::RET_TYPE_MAPPING)),
            ],
            'desc'     => 'string',
        ]);
    }
    
    /**
     *
     * @param Request    $request
     * @param OrderLogic $orderLogic
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function receive(Request $request, OrderLogic $orderLogic)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'order_no' => [
                'required',
                'string',
                Rule::exists('xsw_user_order')
                    ->where('user_id', $user->id)
                    ->where('status', OrderModel::S_SHIPPED),
            ],
        ], [
            'order_no.exists' => '订单不存在或者已经收货了哦~',
        ]);
        
        $orderLogic->receiveOrder($request->input('order_no'));
        
        return json_response([], '操作成功');
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function hide(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'order_no' => [
                'required',
                'string',
                Rule::exists('xsw_user_order')
                    ->where('user_id', $user->id),
//                    ->whereIn('status', [
//                        OrderModel::S_CANCELED,
//                        OrderModel::S_SHARED,
//                    ]),
            ],
        ]);
        
        /** @var OrderModel $order */
        $order = OrderModel::query()
            ->where('order_no', $request->input('order_no'))
            ->first();
        
        $order->update([
            'is_deleted' => true,
        ]);
        
        return json_response([], '操作成功');
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function cancel(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'order_no' => [
                'required',
                'string',
                Rule::exists('xsw_user_order')
                    ->where('user_id', $user->id)
                    ->where('status', OrderModel::S_CREATED),
            ],
        ]);
        
        /** @var OrderModel $order */
        $order = OrderModel::query()
            ->where('order_no', $request->input('order_no'))
            ->first();
        
        $order->update([
            'status' => OrderModel::S_CANCELED,
        ]);
        
        return json_response([], '操作成功');
    }
    
    /**
     * @param Request       $request
     *
     * @param UserCacheRepo $userCacheRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateLogistic(Request $request, UserCacheRepo $userCacheRepo)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'order_no' => [
                'required',
                'string',
                Rule::exists('xsw_user_order')
                    ->where('user_id', $user->id),
            ],
        ]);
        
        /** @var OrderModel $order */
        $order = OrderModel::query()
            ->where('order_no', $request->input('order_no'))
            ->first();
        
        $lastUpdateTs = $userCacheRepo->getLogisticQueryTs($user->id, $order->logistic_no);
        
        if (!empty($lastUpdateTs)) {
            $lastUpdateTs = Carbon::createFromTimestamp($lastUpdateTs);
            if ($lastUpdateTs->diffInHours() <= OrderModel::LOGISTIC_QUERY_GAP_IN_HOUR
                && !empty($order->logistic_info)
            ) {
                // query too frequently
                return json_response($order->logistic_info);
            }
        }
        
        if (empty($order->logistic_no) || empty($order->logistic_abbr)) {
            return json_response([], '卖家暂未填写物流单号', 404);
        }
        
        $result = LogisticInfoService::request(
            $order->logistic_no,
            $order->logistic_abbr
        );
        
        $order->update([
            'logistic_info' => $result,
        ]);
        
        $userCacheRepo->setLogisticQueryTs($user->id, $order->logistic_no);
        
        return json_response($result);
    }

    /**
     * @param Request   $request
     * @param OrderRepo $orderRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function goodsinfo(Request $request, OrderRepo $orderRepo)
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
        ]);

        $res = $orderRepo->goodsinfo($request->input('order_goods_id'));
        //unset($res['snapshot']);
        return json_response($res);
    }

}
