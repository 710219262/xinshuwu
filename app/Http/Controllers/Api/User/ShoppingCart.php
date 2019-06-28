<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/03/2019
 * Time: 23:50
 */

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Logics\Order\OrderLogic;
use App\Models\GoodsInfo;
use App\Models\User;
use App\Models\UserShoppingCart;
use App\Models\UserAddress;
use App\Repos\Goods\GoodsRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShoppingCart extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function add(Request $request)
    {
        $this->validate($request, [
            //todo goods status should verify too
            'goods_id' => 'required|int|exists:xsw_goods_info,id',
            'count'    => 'required|int|min:1',
            // input sku id should belong to related goods
            'sku_id'   => [
                'required',
                'int',
                Rule::exists('xsw_goods_sku', 'id')
                    ->where('goods_id', $request->input('goods_id'))
                    ->whereNull('deleted_at'),
            ],
        ]);
        
        /** @var User $user */
        $user = $request->user();
        
        $goodsId = $request->input('goods_id');
        $skuId = $request->input('sku_id');
        $count = $request->input('count');
        $aff = $request->input('aff', '');

        $builder = $user->shoppingCartRlt()
            ->where('goods_id', $goodsId)
            ->where('sku_id', $skuId);
        
        // if this skus already in shopping cart, just increment it's count
        
        if ($builder->count() > 0) {
            $builder->first()
                ->increment('count', $count);
        } else {
            /** @var GoodsInfo $goods */
            $goods = GoodsInfo::query()->find($goodsId);
            //else create new one
            $user->shoppingCartRlt()->create([
                'goods_id' => $goodsId,
                'store_id' => $goods->store_id,
                'sku_id'   => $skuId,
                'count'    => $count,
                'aff'      => $aff
            ]);
        }
        
        return json_response($user->shoppingCart());
    }
    
    /**
     * @param Request    $request
     *
     * @param OrderLogic $orderLogic
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function calc(Request $request, OrderLogic $orderLogic)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'cart_ids'   => 'required|array',
            'cart_ids.*' => [
                'required',
                'int',
                Rule::exists('xsw_user_shopping_cart', 'id')
                    ->where('user_id', $user->id),
            ],
//            'address_id' => [
//                'required',
//                'int',
//                Rule::exists('xsw_user_address', 'id')
//                    ->where('user_id', $user->id),
//            ],
        ]);
        
        $cartItems = UserShoppingCart::query()
            ->whereIn('id', $request->input('cart_ids'))
            ->where('user_id', $user->id)
            ->get()
            ->toArray();
        
        $price = $orderLogic->calcOrder($cartItems);
        $count = count($cartItems);
        //$addressInfo = UserAddress::query()->find($request->input('address_id'));
        return json_response([
            'shoppingcart' => $user->shoppingCartByIds($request->input('cart_ids')),
            'price' => $price,
            'count' => $count,
            //'address' => $addressInfo
        ]);
    }

    /**
     * @param Request    $request
     *
     * @param OrderLogic $orderLogic
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function instantcalc(Request $request, GoodsRepo $goodsRepo)
    {
        /** @var User $user */
//        $user = $request->user();

        $this->validate($request, [
            'sku_id' => [
                'required',
                'int',
                Rule::exists('xsw_goods_sku', 'id')
            ],
            'count' =>  'required|int|min:1',
//            'address_id' => [
//                'required',
//                'int',
//                Rule::exists('xsw_user_address', 'id')
//                    ->where('user_id', $user->id),
//            ],
        ]);

        $sku_goods_info = $goodsRepo->getGoodsSkuInfo($request->input('sku_id'));
        //$addressInfo = UserAddress::query()->find($request->input('address_id'));
        $price = 0.0;
        $price = my_mul($sku_goods_info->price, $request->input('count'));
        return json_response([
            'sku_goods_info' => $sku_goods_info,
            'price' => $price,
            'count' => $request->input('count'),
            //'address' => $addressInfo
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'id'    => [
                'required',
                'int',
                Rule::exists('xsw_user_shopping_cart', 'id')
                    ->where('user_id', $user->id),
            ],
            'count' => 'required|int|min:1',
        ]);
        
        $user->shoppingCartRlt()
            ->find($request->input('id'))
            ->update([
                'count' => $request->input('count'),
            ]);
        
        return json_response($user->shoppingCart());
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function list(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        return json_response($user->shoppingCart());
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delete(Request $request)
    {
        
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'ids'   => 'required|array',
            'ids.*' => [
                'required',
                'int',
                Rule::exists('xsw_user_shopping_cart', 'id')
                    ->where('user_id', $user->id),
            ],
        ]);
        
        $user->shoppingCartRlt()
            ->whereIn('id', $request->input('ids'))
            ->delete();
        
        return json_response($user->shoppingCart());
    }
    
    /**
     * @param GoodsRepo $goodsRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function recommend(GoodsRepo $goodsRepo)
    {
        return json_response($goodsRepo->getRecommendViaShoppingCart());
    }
}
