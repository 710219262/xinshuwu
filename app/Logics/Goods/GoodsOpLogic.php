<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 08/03/2019
 * Time: 14:32
 */

namespace App\Logics\Goods;

use App\Models\GoodsImg;
use App\Models\GoodsInfo;
use App\Models\GoodsSku;
use App\Models\GoodsSpecGroup;
use App\Models\GoodsSpecValue;
use App\Repos\Goods\GoodsRepo;

class GoodsOpLogic
{
    protected $goodsRepo;
    
    public function __construct(GoodsRepo $goodsRepo)
    {
        $this->goodsRepo = $goodsRepo;
    }
    
    /**
     * @param $data
     *
     * @throws \Exception
     */
    public function createGoods($data)
    {
        try {
            \DB::beginTransaction();
            /**
             * 1.创建商品基本信息 goods_info
             */
            /** @var GoodsInfo $goodsInfo */
            $goodsInfo = $this->goodsRepo->createOne(
                $data['store_id'],
                $data['category_id'],
                $data['name'],
                $data['type'],
                $data['sku_values'],
                array_get($data, 'status', GoodsInfo::S_ON_SALE)
            );
            
            $goodsId = $goodsInfo->id;
            
            $skus = array_get($data, 'skus', []);
            
            /**
             * 如果没有带规格的商品，创建一个规格组id为0的规格，即默认规格
             */
            if (0 === count($skus)) {
                $skus [] = [
                    'inventory'    => $data['inventory'],
                    'price'        => $data['price'],
                    'market_price' => $data['market_price'],
                ];
            }
            
            $price = strval(min(array_pluck($skus, 'price')));
            $market_price = strval(min(array_pluck($skus, 'market_price')));
            $inventory = min(array_pluck($skus, 'inventory'));
            
            // 最低价格
            $goodsInfo->update([
                'price'        => $price,
                'market_price' => $market_price,
                'inventory'    => $inventory,
            ]);
            
            //*********************************************
            
            /**
             * 2.创建商品规格信息 goods_sku
             */
            $this->createSkus($skus, $goodsId);
            
            //*********************************************
            
            /**
             * 3.创建商品图片信息 goods_img
             */
            
            $this->createGoodsImgs(
                array_get($data, 'banner_imgs', []),
                $goodsId,
                GoodsImg::T_BANNER
            );
            
            $this->createGoodsImgs(
                array_get($data, 'info_imgs', []),
                $goodsId,
                GoodsImg::T_INFO
            );
            
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception(sprintf("创建商品失败:%s", $e));
        }
    }
    
    /**
     * @param $data
     *
     * @throws \Exception
     */
    public function updateGoods($data)
    {
        try {
            \DB::beginTransaction();
            /**
             * 1.更新商品基本信息 goods_info
             */
            /** @var GoodsInfo $goodsInfo */
            $goodsInfo = $this->goodsRepo->updateOne(
                $data['id'],
                $data['category_id'],
                $data['name'],
                $data['type'],
                $data['sku_values'],
                array_get($data, 'status', GoodsInfo::S_ON_SALE)
            );
            
            $goodsId = $goodsInfo->id;
            
            $skus = array_get($data, 'skus', []);
            /**
             * 如果没有带规格的商品，创建一个规格组id为0的规格，即默认规格
             */
            if (0 === count($skus)) {
                $skus [] = [
                    'inventory'    => $data['inventory'],
                    'price'        => strval($data['price']),
                    'market_price' => strval($data['market_price']),
                ];
                
                /**
                 * 无规格商品更新
                 * todo fixme
                 * this just hack method
                 */
            } elseif (1 === count($skus) && isset($skus[0]['has_spec']) && $skus[0]['has_spec'] === false) {
                $skus[0] = [
                    'inventory'    => $data['inventory'],
                    'price'        => strval($data['price']),
                    'market_price' => strval($data['market_price']),
                ];
            }
            
            $price = strval(min(array_pluck($skus, 'price')));
            $inventory = min(array_pluck($skus, 'inventory'));
            $market_price = strval(min(array_pluck($skus, 'market_price')));
            
            // 最低价格和库存
            $goodsInfo->update([
                'price'        => $price,
                'market_price' => $market_price,
                'inventory'    => $inventory,
            ]);
            
            
            /**
             * 2.删除并创建商品规格信息 goods_sku
             */
            //删除之前的规格
            $goodsInfo->skuRlt()->delete();
            
            $this->createSkus($skus, $goodsId);
            
            /**
             * 3.创建商品图片信息 goods_img
             */
            
            $goodsInfo->bannerImgsRlt()->delete();
            $goodsInfo->infoImgsRlt()->delete();
            
            $this->createGoodsImgs(
                array_get($data, 'banner_imgs', []),
                $goodsId,
                GoodsImg::T_BANNER
            );
            
            $this->createGoodsImgs(
                array_get($data, 'info_imgs', []),
                $goodsId,
                GoodsImg::T_INFO
            );
            
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception(sprintf("更新商品失败:%s", $e));
        }
    }
    
    /**
     * 创建sku
     *
     * @param $skus
     * @param $goodsId
     */
    protected function createSkus(array $skus, $goodsId)
    {
        foreach ($skus as $sku) {
            $specValueIds = array_get($sku, 'specs', []);
            $skuName = '默认';
            
            //创建sku
            /** @var GoodsSku $goodsSku */
            $goodsSku = GoodsSku::query()->create([
                'goods_id'     => $goodsId,
                'has_spec'     => count($specValueIds) > 0,
                'inventory'    => $sku['inventory'],
                'price'        => strval($sku['price']),
                'market_price' => strval($sku['market_price']),
            ]);
            
            if (count($specValueIds) > 0) {
                $specsNameArr = [];
                foreach ($specValueIds as $specValueId) {
                    /** @var GoodsSpecValue $specValue */
                    $specValue = GoodsSpecValue::query()
                        ->find($specValueId);
                    //specValue冗余存储库存和用于前端购物车
                    array_push($specsNameArr, $specValue->value);
                    //同时创建spec组
                    GoodsSpecGroup::query()->create([
                        'sku_id' => $goodsSku->id,
                        'sv_id'  => $specValueId,
                        'sv'     => $specValue->value,
                    ]);
                }
                //拼接成: "红|XL"
                $skuName = implode("|", $specsNameArr);
            }
            
            $goodsSku->update([
                'sku_name' => $skuName,
            ]);
        }
    }
    
    /**
     * 创建商品图片
     *
     * @param $imgs
     * @param $goodsId
     * @param $type
     */
    protected function createGoodsImgs($imgs, $goodsId, $type)
    {
        foreach ($imgs as $url) {
            GoodsImg::query()->create([
                'goods_id' => $goodsId,
                'url'      => $url,
                'type'     => $type,
            ]);
        }
    }

    /**
     * @param $data
     *
     * @throws \Exception
     */
    public function deleteGoods($data)
    {
            $this->goodsRepo->deleteOne($data['id'],$data['deleted_at']);
            //return json_response([], "删除成功");
    }
}
