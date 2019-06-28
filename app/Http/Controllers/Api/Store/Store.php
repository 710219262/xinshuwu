<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 25/03/2019
 * Time: 12:57
 */

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\GoodsInfo;
use App\Models\MerchantAccount;
use App\Repos\Goods\GoodsRepo;
use Illuminate\Http\Request;

class Store extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     * @throws \Illuminate\Validation\ValidationException
     */
    public function info(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_merchant_account',
        ]);

        /** @var MerchantAccount $merchant */
        $merchant = MerchantAccount::query()->find($request->input('id'));

        return json_response([
            'id'           => $merchant->id,
            'name'         => $merchant->name,
            'logo'         => $merchant->logo,
            'desc'         => $merchant->desc,
            'is_collected' => $merchant->is_collected,
            'collect'      => $merchant->collect,
            'sale_count'   => GoodsInfo::query()
                ->where('store_id', $merchant->id)
                ->count(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @param GoodsRepo $goodsRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function goodsList(Request $request, GoodsRepo $goodsRepo)
    {
        $this->validate($request, [
            'id'    => 'required|int|exists:xsw_merchant_account,id',
            'price' => 'string|in:asc,desc',
        ]);

        $goodsList = $goodsRepo->getListForCustomer(
            array_filter($request->only(['id', 'price']))
        );

        return json_response($goodsList);
    }
}
