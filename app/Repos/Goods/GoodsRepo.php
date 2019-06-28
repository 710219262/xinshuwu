<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 08/03/2019
 * Time: 14:36
 */

namespace App\Repos\Goods;

use App\Models\GoodsInfo;
use App\Models\GoodsSku;
use App\Models\GoodsSpec;
use Illuminate\Database\Query\Builder;

class GoodsRepo
{
    /**
     * @param $storeId
     * @param $categoryId
     * @param $name
     * @param $type
     *
     * @param $skuValues
     *
     * @param $status
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function createOne($storeId, $categoryId, $name, $type, $skuValues, $status)
    {
        return GoodsInfo::query()->create([
            'store_id'    => $storeId,
            'category_id' => $categoryId,
            'name'        => $name,
            'type'        => $type,
            'sku_values'  => $skuValues,
            'status'      => $status,
        ]);
    }
    
    /**
     * @param $goodsId
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getOne($goodsId)
    {
        return GoodsInfo::query()
            ->with([
                'store' => function ($q) {
                    /** @var Builder $q */
                    $q->select([
                        'id',
                        'phone',
                        'name',
                    ]);
                },
            ])
            ->select([
                'id',
                'category_id',
                'store_id',
                'name',
                'type',
                'sku_values',
                'inventory',
                'price',
                'market_price',
                'created_at',
                'updated_at',
                'status',
            ])
            ->find($goodsId)
            ->append([
                'banner_imgs',
                'info_imgs',
                'sku',
                'share_users',
            ]);
    }
    
    /**
     * 给终端用户的商品详情
     *
     * @param $goodsId
     *
     * @return array|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getOneForUser($goodsId)
    {
        $goodsInfo = $this->getOne($goodsId);
        
        $goodsInfo = $goodsInfo->toArray();
        
        $goodsInfo['sku_values'] = $this->formatSkuValues($goodsInfo['sku_values']);
        
        return $goodsInfo;
    }
    
    /**
     * @param integer $categoryId
     * @param integer $limit
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getRecommendForUser($categoryId, $limit = 3)
    {
        $goodsList = GoodsInfo::query()
            ->where('category_id', $categoryId)
            ->orderBy(\DB::raw('RAND()'))
            ->limit($limit)
            ->select([
                'id',
                'category_id',
                'name',
                'price',
            ])->get();
        
        //append images
        for ($i = 0; $i < count($goodsList); $i++) {
            $imgs = $goodsList[$i]->banner_imgs;
            $goodsList[$i]['img'] = count($imgs) ? $imgs[0] : '';
            $goodsList[$i]->append('share_users');
        }
        
        return $goodsList;
    }
    
    /**
     * In future recommend via data mining
     *
     * @param integer $limit
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getRecommendViaShoppingCart($limit = 20)
    {
        $goodsList = GoodsInfo::query()
            ->orderBy(\DB::raw('RAND()'))
            ->limit($limit)
            ->select([
                'id',
                'category_id',
                'name',
                'price',
            ])->get();
        
        //append images
        for ($i = 1; $i < count($goodsList); $i++) {
            $imgs = $goodsList[$i]->banner_imgs;
            $goodsList[$i]['img'] = count($imgs) ? $imgs[0] : '';
        }
        
        return $goodsList;
    }
    
    /**
     * @param $skuValues
     *
     * @return array
     *              [
     *                          {
     *                              "name": "型号",
     *                              "values": [
     *                                  "2018",
     *                                  "2017"
     *                              ]
     *                          },
     *                          {
     *                              "name": "容量",
     *                              "values": [
     *                                  "256GB",
     *                                  "512GB"
     *                              ]
     *                          }
     *                      ]
     *              ]
     */
    protected function formatSkuValues($skuValues)
    {
        $specs = array_get($skuValues, 'specs', []);
        $ids = array_column($specs, 'spec_id');
        $ids = empty($ids) ? [] : $ids;
        $values = array_get($skuValues, 'values', []);
        $specNames = GoodsSpec::query()->whereIn('id', $ids)->pluck('name')->toArray();
        
        $result = [];
        
        for ($i = 0; $i < count($specNames); $i++) {
            $result[] = [
                'name'   => $specNames[$i],
                'values' => array_pluck($values[$i], 'value'),
            ];
        }
        
        return $result;
    }

    /**
     * For mms list
     *
     * @param $query
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getList($query, $offset = 0, $pageSize = 0)
    {
        $builder = GoodsInfo::query();
        $builder->with(['store']);
        if ($storeId = array_get($query, 'store_id')) {
            $builder->where('store_id', $storeId);
        }
        
        if ($categoryId = array_get($query, 'category_id')) {
            $builder->where('category_id', $categoryId);
        }
        
        if ($status = array_get($query, 'status')) {
            $builder->where('status', $status);
        }
        
        if ($priceMin = array_get($query, 'price_min')) {
            $builder->whereHas('skuRlt', function ($query) use ($priceMin) {
                /** @var Builder $query */
                $query->where('price', '>=', strval($priceMin));
            });
        }
        
        if ($priceMax = array_get($query, 'price_max')) {
            $builder->whereHas('skuRlt', function ($query) use ($priceMax) {
                /** @var Builder $query */
                $query->where('price', '<=', strval($priceMax));
            });
        }
        if ($goodsName = array_get($query, 'goods_name')) {
            $builder->where('name', 'LIKE', "%{$goodsName}%");
        }
        $goodsTotal = $builder->count();

        //暂时考虑兼容性
        if(!empty($pageSize)) {
            $builder->offset($offset)->limit($pageSize);
        }

        $goodsList = $builder->get();
        
        //append images
        for ($i = 0; $i < count($goodsList); $i++) {
            $imgs = $goodsList[$i]->banner_imgs;
            $goodsList[$i]['img'] = count($imgs) ? $imgs[0] : '';
        }
        //暂时考虑兼容性
        if(empty($pageSize)) {
            return $goodsList;
        }
        return ['total'=>$goodsTotal,'list'=>$goodsList];
    }
    
    /**
     * For user list
     *
     * @param $data
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getListForCustomer($data)
    {
        $builder = GoodsInfo::query();

        if ($categoryId = array_get($data, 'category_id')) {
            $builder->where('category_id', $categoryId);
        }
        
        if ($storeId = array_get($data, 'id')) {
            $builder->where('store_id', $storeId);
        }
        
        if ($keywords = array_get($data, 'keywords')) {
            $builder->where('name', 'LIKE', "%{$keywords}%");
        }

        if (array_get($data, 'from_page') === 'mall') {
            $builder->orderBy(\DB::raw('RAND()'));
        } else {
            if ($direction = array_get($data, 'price')) {
                $builder->orderBy('price', $direction);
            } else {
                $builder->orderBy('id', 'desc');
            }
        }

        $builder->where('status', GoodsInfo::S_ON_SALE);
        
        $goodsList = $builder->select([
            'id',
            'store_id',
            'name',
            'type',
            'price',
            'market_price',
        ])->get();
        
        return $goodsList;
    }
    
    /**
     *
     * @param $goodsId
     * @param $categoryId
     * @param $name
     * @param $type
     *
     * @param $skuValues
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function updateOne($goodsId, $categoryId, $name, $type, $skuValues, $status)
    {
        $goods = GoodsInfo::query()->find($goodsId);
        $goods->update([
            'category_id' => $categoryId,
            'name'        => $name,
            'type'        => $type,
            'sku_values'  => $skuValues,
            'status'      => $status,
        ]);
        
        return $goods;
    }

    /**
     *
     * @param $goodsId
     * @param $delete_at
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function deleteOne($goodsId, $deleted_at)
    {
        $goods = GoodsInfo::query()->find($goodsId);
        $goods->update([
            'deleted_at' => date("Y-m-d H:i:s",$deleted_at),
        ]);
        return $goods;
    }

    /**
     * @param $skuid
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getGoodsSkuInfo($skuid)
    {
        return GoodsSku::query()
            ->with([
                'goodsinfo' => function ($q) {
                    /** @var Builder $q */
                    $q->select([
                        'id',
                        'name',
                    ]);
                },
            ])
            ->find($skuid);
    }
}
