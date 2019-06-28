<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/03/2019
 * Time: 21:22
 */

namespace App\Http\Controllers\MMS\Admin;

use App\Http\Controllers\Controller;
use App\Logics\Goods\GoodsOpLogic;
use App\Models\GoodsInfo;
use App\Models\GoodsSpec;
use App\Repos\Goods\GoodsRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Goods extends Controller
{
    /**
     * @param Request   $request
     *
     * @param GoodsRepo $goodsRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getList(Request $request, GoodsRepo $goodsRepo)
    {
        $this->validate($request, [
            'query'             => 'array',
            'query.id'          => 'integer',
            'query.store_id'    => 'int|exists:xsw_merchant_account,id',
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
        if(!empty($request->input('ispage'))) {
            $pageSize = empty($request->input('pageSize')) ? 10 : $request->input('pageSize');
            $pageIndex = empty($request->input('pageIndex')) ? 1 : $request->input('pageIndex');
            $offset = ceil($pageSize * ($pageIndex - 1));
            $goodsList = $goodsRepo->getList($request->input('query'), $offset, $pageSize);
        }else{
            $goodsList = $goodsRepo->getList($request->input('query'));
        }
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
            'id' => 'required|int|exists:xsw_goods_info',
        ]);
        
        $goodsInfo = $goodsRepo->getOne($request->input('id'));
        
        return json_response($goodsInfo);
    }
    
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
                Rule::exists('xsw_goods_info', 'id'),
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
