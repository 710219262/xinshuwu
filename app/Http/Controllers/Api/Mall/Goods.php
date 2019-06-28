<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 25/03/2019
 * Time: 11:17
 */

namespace App\Http\Controllers\Api\Mall;

use App\Http\Controllers\Controller;
use App\Repos\Goods\GoodsRepo;
use Illuminate\Http\Request;

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
    public function list(Request $request, GoodsRepo $goodsRepo)
    {
        $this->validate($request, [
            'category_id' => 'int|exists:xsw_goods_category,id',
            'keywords'    => 'string',
        ]);

        $data = array_filter(
            $request->only(['category_id', 'keywords'])
        );
        $goodsList = $goodsRepo->getListForCustomer(array_merge($data, [
            'from_page' => 'mall'
        ]));
        
        return json_response($goodsList);
    }
    
    /**
     * @param Request   $request
     * @param GoodsRepo $goodsRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function info(Request $request, GoodsRepo $goodsRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_goods_info',
        ]);
        
        $goodsInfo = $goodsRepo->getOneForUser($request->input('id'));

        return json_response($goodsInfo);
    }
    
    /**
     * @param Request   $request
     * @param GoodsRepo $goodsRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function recommend(Request $request, GoodsRepo $goodsRepo)
    {
        $this->validate($request, [
            'category_id' => 'required|int|exists:xsw_goods_category,id',
        ]);
        
        $goodsInfo = $goodsRepo->getRecommendForUser($request->input('category_id'));
        
        return json_response($goodsInfo);
    }
}
