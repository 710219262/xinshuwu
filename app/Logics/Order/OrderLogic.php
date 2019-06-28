<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 27/03/2019
 * Time: 19:58
 */

namespace App\Logics\Order;

use App\Events\Order\OrderWasUpdated;
use App\Models\GoodsInfo;
use App\Models\GoodsSku;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserShoppingCart;
use Laravel\Lumen\Http\Request;
use Yansongda\Pay\Gateways\Alipay;
use Yansongda\Pay\Gateways\Wechat;

class OrderLogic
{
    /**
     * @param User  $user
     * @param array $data
     *
     *                   [
     *                     'cart_ids' => [1,2,3]
     *                     'address_id' => 1
     *                   ]
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function createOrderViaCart(User $user, array $data)
    {
        try {
            \DB::beginTransaction();
            $cartIds = array_get($data, 'cart_ids', []);
            $addrId = array_get($data, 'address_id');
            /** @var UserAddress $addr */
            $addr = UserAddress::query()->find($addrId);
            
            $storeIdToCarts = UserShoppingCart::query()
                ->whereIn('id', $cartIds)
                ->where('user_id', $user->id)
                ->get()
                ->groupBy('store_id')
                ->toArray();
            
            $batchNo = Order::newBatchNum($user->phone);
            //todo some item maybe invalid or soldout when create order
            foreach ($storeIdToCarts as $storeId => $cartItems) {
                $this->createOrderForEachMerchant($user, $batchNo, $storeId, $addr, $cartItems);
            }
            
            // delete user shopping cart related items
            UserShoppingCart::query()
                ->whereIn('id', $cartIds)
                ->where('user_id', $user->id)
                ->delete();
            
            \DB::commit();
            return json_response(['batch_no' => $batchNo], '订单创建成功');
        } catch (\Exception $e) {
            \DB::rollBack();
            return json_response([], "订单创建失败：{$e->getMessage()}", 500);
        }
    }
    
    /**
     * @param User  $user
     * @param array $data
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function createOrderInstant(User $user, array $data)
    {
        try {
            \DB::beginTransaction();
            \DB::commit();
            $goodsId = array_get($data, 'goods_id', '');
            $skuId = array_get($data, 'sku_id', '');
            $count = array_get($data, 'count', 0);
            
            if (empty($goodsId) || empty($skuId) || $count < 1) {
                throw new \Exception("商品或数量有误");
            }
            $addrId = array_get($data, 'address_id');
            /** @var UserAddress $addr */
            $addr = UserAddress::query()->find($addrId);
            
            /** @var GoodsInfo $goods */
            $goods = GoodsInfo::query()->find($goodsId);
            
            $batchNo = Order::newBatchNum($user->phone);
            
            // mock shopping items array
            // the rest logic are exactly same
            $this->createOrderForEachMerchant($user, $batchNo, $goods->store_id, $addr, [
                [
                    'goods_id' => $goodsId,
                    'sku_id'   => $skuId,
                    'count'    => $count,
                    'aff'      => Request::capture()->get('aff', ''),
                ],
            ]);
            
            return json_response(['batch_no' => $batchNo], '订单创建成功');
        } catch (\Exception $e) {
            \DB::rollBack();
            return json_response([], "订单创建失败：{$e->getMessage()}", 500);
        }
    }
    
    /**
     * @param $orderNo
     *
     * @param $isBatch
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function prepayViaWechat($orderNo, $isBatch)
    {
        /** @var Wechat $wechat */
        $wechat = app('pay.wechat');
        $column = $isBatch ? 'batch_no' : 'order_no';

        $pay_amount = Order::query()
            ->where($column, $orderNo)
            ->sum('pay_amount');


        Order::query()
            ->where($column, $orderNo)
            ->update([
                'pay_method' => Order::P_WECHAT
            ]);

        $data = [
            'out_trade_no' => $orderNo,
            'body'         => Order::DEFAULT_BODY,
            'total_fee'    => yuan_to_fen($pay_amount),
            'attach'       => json_encode(['is_batch' => $isBatch]),
        ];
        
        $content = $wechat->app($data)->getContent();
        
        return json_response(json_decode($content, true));
    }

    /**
     * @param $orderNo
     * 公众号支付
     * @param $isBatch
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function prepayViaWechatJsapi($orderNo, $isBatch, $openid)
    {
        /** @var Wechat $wechat */
        $wechat = app('pay.wechat');
        $column = $isBatch ? 'batch_no' : 'order_no';

        $pay_amount = Order::query()
            ->where($column, $orderNo)
            ->sum('pay_amount');


        Order::query()
            ->where($column, $orderNo)
            ->update([
                'pay_method' => Order::P_WECHAT
            ]);

        $data = [
            'out_trade_no' => $orderNo,
            'body'         => Order::DEFAULT_BODY,
            'total_fee'    => yuan_to_fen($pay_amount),
            'openid' => $openid,
            'attach'       => json_encode(['is_batch' => $isBatch]),
        ];

        $content = $wechat->mp($data);
        if(!empty($content)) {
            $content['pay_amount'] = $pay_amount;
        }

        return json_response(json_decode($content, true));
    }
    
    /**
     * @param $orderNo
     *
     * @param $isBatch
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function prepayViaAli($orderNo, $isBatch)
    {
        /** @var Alipay $alipay */
        $alipay = app('pay.alipay');
        
        $column = $isBatch ? 'batch_no' : 'order_no';

        $pay_amount = Order::query()
            ->where($column, $orderNo)
            ->sum('pay_amount');


        Order::query()
            ->where($column, $orderNo)
            ->update([
                'pay_method' => Order::P_ALI
            ]);

        $data = [
            'out_trade_no'    => $orderNo,
            'subject'         => Order::DEFAULT_BODY,
            'total_amount'    => sprintf("%.2f", $pay_amount),
            'passback_params' => ['is_batch' => $isBatch],
            'timeout_express' => '30m',
        ];
        
        $content = $alipay->app($data)->getContent();
        
        return json_response($content);
    }
    
    /**
     * @param $user             User
     * @param $batchNo
     * @param $storeId
     * @param $addr
     * @param $cartItems        array [
     *                          [
     *                          'goods_id',
     *                          'sku_id',
     *                          'count'
     *                          ]....
     *                          ]
     *
     * @return string
     */
    protected function createOrderForEachMerchant($user, $batchNo, $storeId, $addr, $cartItems)
    {
        $orderNo = Order::newOrderNum($user->phone);
        
        $isVip = $user->isVip();
        
        $goodsPrice = $this->calcOrder($cartItems);
        // this version all order price included delivery fees
        // so total amount just simply equal to goods price
        $totalAmount = $goodsPrice;
        $discount = doubleval(config('xsw.vip_discount'));
        $payAmount = $isVip ? my_mul($goodsPrice, $discount) : $goodsPrice;
        //$deliveryPrice = 0;
        //$payAmount +=$deliveryPrice
        
        Order::query()->create([
            'batch_no'     => $batchNo,
            'order_no'     => $orderNo,
            'user_id'      => $user->id,
            'store_id'     => $storeId,
            'status'       => Order::S_CREATED,
            'address'      => $addr,
            'total_amount' => $totalAmount,
            // is_vip ? pay amount = total amount * vip_discount
            'pay_amount'   => $payAmount,
            'goods_price'  => $goodsPrice,
            'payload'      => $this->buildPayload(),
            'discount'     => $isVip ? 'YES' : 'NO',
            // aff link should be encrypted
            // when order
        ]);
        
        $this->takeGoodsSnapshot($user, $orderNo, $cartItems, $isVip);
        
        return $orderNo;
    }
    
    /**
     * @return array
     */
    protected function buildPayload()
    {
        $request = Request::capture();
        
        return [
            'body'    => $request->all(),
            'url'     => $request->url(),
            'ip'      => $request->getClientIp(),
            'headers' => $request->headers->all(),
            'version' => config('xsw.order_version'),
        ];
    }
    
    /**
     * @param $user User
     * @param $orderNo
     * @param $cartItems
     *
     * @param $isVip
     *
     * @return array
     */
    protected function takeGoodsSnapshot($user, $orderNo, $cartItems, $isVip)
    {
        $snapshot = [];
        
        foreach ($cartItems as $cartItem) {
            $goodsId = $cartItem['goods_id'];
            $skuId = $cartItem['sku_id'];
            $count = intval($cartItem['count']);
            
            /** @var GoodsSku $sku */
            $sku = GoodsSku::query()->find($skuId);
            /** @var GoodsInfo $goodsInfo */
            $goodsInfo = GoodsInfo::query()->find($goodsId);
            $goodsInfo->append(['info_imgs', 'banner_imgs', 'sku']);

            $goodsPrice = strval(doubleval($sku->price));
            $totalAmount = my_mul($goodsPrice, $count);
            $discount = strval(doubleval(config('xsw.vip_discount')));
            
            $payAmount = $isVip ? my_mul($totalAmount, $discount) : $totalAmount;
            $perPrice = $isVip ? my_mul($goodsPrice, $discount) : $goodsPrice;

            $aff = array_get($cartItem, 'aff', '');
            
            OrderGoods::query()->create([
                'user_id'       => $user->id,
                'goods_id'      => $goodsId,
                'sku_id'        => $skuId,
                'order_no'      => $orderNo,
                'snapshot'      => [
                    'goods_info' => $goodsInfo,
                    'sku'        => $sku,
                ],
                'count'         => $count,
                'total_amount'  => $totalAmount,
                'pay_amount'    => $payAmount,
                'org_per_price' => $goodsPrice,
                'pay_per_price' => $perPrice,
                'aff'           => $aff,
            ]);
        }
        
        return $snapshot;
    }
    
    /**
     * @param $cartItems
     *
     * @return float|int
     */
    public function calcOrder($cartItems)
    {
        $price = 0.0;
        foreach ($cartItems as $cartItem) {
            $skuId = $cartItem['sku_id'];
            $count = intval($cartItem['count']);
            
            /** @var GoodsSku $sku */
            $sku = GoodsSku::query()->find($skuId);
            $price = my_add($price, my_mul($sku->price, $count));
        }
        
        return $price;
    }
    
    /**
     * @param $orderNo
     *
     * @throws \Exception
     */
    public function receiveOrder($orderNo)
    {
        /** @var Order $order */
        $order = Order::query()
            ->where('order_no', $orderNo)
            ->first();
        try {
            \DB::beginTransaction();
            
            $order->update([
                'status' => Order::S_RECEIVED,
            ]);
            
            event(new OrderWasUpdated($order));
            
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception("操作失败, 请重试");
        }
    }
}
