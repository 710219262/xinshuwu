<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 08/03/2019
 * Time: 00:39
 */

namespace App\Http\Controllers\MMS\Merchant;

use App\Http\Controllers\Controller;
use App\Logics\Goods\GoodsOpLogic;
use App\Models\GoodsInfo;
use App\Models\GoodsSpec;
use App\Models\GoodsSpecValue;
use App\Repos\Goods\GoodsRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Goods extends Controller
{
    /**
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getGoodsSpecList()
    {
        $specList = GoodsSpec::query()->select(['id', 'name', 'id as title'])
            ->get();
        
        return json_response($specList);
    }
    
    /**
     * @param $id
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getGoodsSpecInfo($id)
    {
        $specInfo = GoodsSpec::query()->select(['id', 'name', 'id as title'])
            ->find($id);
        
        return json_response($specInfo);
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function addGoodsSpec(Request $request)
    {
        $this->validate($request, [
            'spec_id' => 'required|int|exists:xsw_goods_spec,id',
            'value'   => 'required|string',
        ]);
        
        if (str_contains($request->input('value'), "|")) {
            return json_response([], '不能含有|符号', 422);
        }
        
        $data = $request->only(['spec_id', 'value']);
        
        $spec = GoodsSpecValue::query()
            ->firstOrCreate($data)
            ->only(['id', 'value']);
        
        return json_response($spec);
    }
    
    /**
     * @param Request      $request
     *
     * @param GoodsOpLogic $goodsOpLogic
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function createGoods(Request $request, GoodsOpLogic $goodsOpLogic)
    {
        $this->validate($request, [
            'name'             => 'required|string|max:255',
            'category_id'      => 'required|int|exists:xsw_goods_category,id',
            'skus'             => 'array',
            'skus.*.price'     => 'required|numeric|min:0.01',
            'skus.*.inventory' => 'required|int|min:1',
            'skus.*.specs'     => 'required|array',
            'skus.*.specs.*'   => 'required|int|exists:xsw_goods_spec_value,id',
            'sku_values'       => 'array',
            'banner_imgs'      => 'required|array|between:1,5',
            'banner_imgs.*'    => 'required|string',
            'info_imgs'        => 'required|array|between:1,20',
            'info_imgs.*'      => 'required|string',
            'type'             => 'required|string|in:COMMON,IMPORT',
            'status'           => [
                'string',
                Rule::in([
                    GoodsInfo::S_ON_SALE,
                    GoodsInfo::S_DRAFT,
                ]),
            ],
        ]);
        
        if (0 === count($request->input('skus'))) {
            $this->validate($request, [
                'price'     => 'numeric|min:0.01',
                'inventory' => 'numeric|min:1',
            ]);
        }
        
        $data = $request->only([
            'name',
            'category_id',
            'skus',
            'banner_imgs',
            'info_imgs',
            'type',
            'sku_values',
            'inventory',
            'price',
            'market_price',
            'status',
        ]);
        
        $data['store_id'] = $request->user()->id;
        
        $goodsOpLogic->createGoods($data);
        
        return json_response([], '创建成功');
    }
    
    /**
     * @param Request      $request
     *
     * @param GoodsOpLogic $goodsOpLogic
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function updateGoods(Request $request, GoodsOpLogic $goodsOpLogic)
    {
        $this->validate($request, [
            'id'                  => 'required|int|exists:xsw_goods_info',
            'name'                => 'required|string|max:255',
            'category_id'         => 'required|int|exists:xsw_goods_category,id',
            'skus'                => 'array',
            'skus.*.price'        => 'required|numeric|min:0.01',
            'skus.*.market_price' => 'required|numeric|min:0.01',
            'skus.*.inventory'    => 'required|int|min:1',
            'skus.*.specs'        => 'array',
            'skus.*.specs.*'      => 'required|int|exists:xsw_goods_spec_value,id',
            'sku_values'          => 'array',
            'banner_imgs'         => 'required|array|between:1,5',
            'banner_imgs.*'       => 'required|string',
            'info_imgs'           => 'required|array|between:1,20',
            'info_imgs.*'         => 'required|string',
            'type'                => 'required|string|in:COMMON,IMPORT',
            'status'              => [
                'string',
                Rule::in([
                    GoodsInfo::S_ON_SALE,
                    GoodsInfo::S_DRAFT,
                ]),
            ],
        ]);
        
        if (0 === count($request->input('skus'))) {
            $this->validate($request, [
                'price'        => 'numeric|min:0.01',
                'market_price' => 'numeric|min:0.01',
                'inventory'    => 'numeric|min:1',
            ]);
        }
        
        
        $data = $request->only([
            'id',
            'name',
            'category_id',
            'skus',
            'sku_values',
            'banner_imgs',
            'info_imgs',
            'type',
            'price',
            'market_price',
            'status',
            'inventory',
        ]);
        
        $goodsOpLogic->updateGoods($data);
        
        return json_response([], '更新成功');
    }
    
    /**
     * @param Request   $request
     * @param GoodsRepo $goodsRepo
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getGoodsList(Request $request, GoodsRepo $goodsRepo)
    {
        $this->validate($request, [
            'query'             => 'array',
            'query.id'          => 'integer',
            'query.category_id' => 'int|exists:xsw_goods_category,id',
            'query.status'      => [
                Rule::in([
                    GoodsInfo::S_ON_SALE,
                    GoodsInfo::S_DRAFT,
                    GoodsInfo::S_SOLDOUT,
                ]),
            ],
            'query.price_min'   => 'numeric',
            'query.price_max'   => 'numeric',
        ]);

        $query = $request->input('query');
        //用于公司写软文提取所有商品数据
        if($request->input('special')){
            return $this->getGoodsListSpecial($request,$goodsRepo);
        }
        $query['store_id'] = $request->user()->id;

        if(!empty($request->input('ispage'))) {
            $pageSize = empty($request->input('pageSize')) ? 10 : $request->input('pageSize');
            $pageIndex = empty($request->input('pageIndex')) ? 1 : $request->input('pageIndex');
            $offset = ceil($pageSize * ($pageIndex - 1));
            $goodsList = $goodsRepo->getList($query, $offset, $pageSize);
        }else{
            $goodsList = $goodsRepo->getList($query);
        }
        
        return json_response($goodsList);
    }

    /**
     * @param Request   $request
     * @param GoodsRepo $goodsRepo
     * 用于公司写软文提取所有商品数据
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getGoodsListSpecial(Request $request, GoodsRepo $goodsRepo)
    {
        $this->validate($request, [
            'query'             => 'array',
            'query.id'          => 'integer',
            'query.category_id' => 'int|exists:xsw_goods_category,id',
            'query.status'      => [
                Rule::in([
                    GoodsInfo::S_ON_SALE,
                    GoodsInfo::S_DRAFT,
                    GoodsInfo::S_SOLDOUT,
                ]),
            ],
            'query.price_min'   => 'numeric',
            'query.price_max'   => 'numeric',
        ]);

        $query = $request->input('query');
        $self_account = [600002,600007,600005];//此三个用户开放软文所有商品权限
        if(!in_array($request->user()->id,$self_account)) {
            $query['store_id'] = $request->user()->id;
        }

        $goodsList = $goodsRepo->getList($query);

        return json_response($goodsList);
    }

    /**
     * @param Request   $request
     *
     * @param GoodsRepo $goodsRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getGoodsInfo(Request $request, GoodsRepo $goodsRepo)
    {
        $this->validate($request, [
            'id' => [
                'required',
                'int',
                Rule::exists('xsw_goods_info', 'id')
                    ->where('store_id', $request->user()->id),
            ],
        ]);

        $goodsInfo = $goodsRepo->getOne($request->input('id'));

        return json_response($goodsInfo);
    }

    /**
     * @param Request   $request
     *
     * @param GoodsRepo $goodsRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function deleteGoods(Request $request, GoodsOpLogic $goodsOpLogic)
    {
        $this->validate($request, [
            'id' => [
                'required',
                'int',
                Rule::exists('xsw_goods_info', 'id')
                    ->where('store_id', $request->user()->id),
            ],
            'deleted_at' => [
                'required',
                'int',
            ],
        ]);
        $data = [];
        $data['id']= $request->input('id');
        $data['deleted_at']= $request->input('deleted_at');

        $goodsOpLogic->deleteGoods($data);

        return json_response([], '删除成功');
    }
}
