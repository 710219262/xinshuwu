<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/02/2019
 * Time: 14:20
 */

namespace App\Repos\Common;

use App\Models\Region;

class RegionRepo
{
    /**
     * 获取region列表
     *
     * @param       $where
     * @param array $keys
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getRegionList($where, $keys = ['*'])
    {
        $builder = Region::query();
        
        
        $builder->where('parent_id', array_get($where, 'parent_id', 0));
        $builder->where('is_foreign', array_get($where, 'is_foreign', 0));
        
        
        $data = $builder->select($keys)->get();
        
        if ($where['is_foreign']) {
            return $data;
        }
        
        foreach ($data as $k => $item) {
            if ($item['level'] < 4) {
                $where['parent_id'] = $item['region_id'];
                $data[$k]['children'] = $this->getRegionList($where, $keys);
                if ($item['level'] == 3) {
                    $data[$k]['children'][] = [
                        'region_id' => $item['region_id'],
                        'name'      => '其他',
                    ];
                }
            }
        }
        
        return $data;
    }
    
    
    /**
     * 获取region列表
     * 特殊处理HK,Macao,TW 三级问题
     *
     * @param       $where
     * @param array $keys
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getRegionListForApp($where, $keys = ['*'])
    {
        $builder = Region::query();
        
        
        $builder->where('parent_id', array_get($where, 'parent_id', 0));
        $builder->where('is_foreign', array_get($where, 'is_foreign', 0));
        
        
        $data = $builder->select($keys)->get();
        
        if ($where['is_foreign']) {
            return $data;
        }
        
        foreach ($data as $k => $item) {
            if ($item['level'] < 4) {
                $where['parent_id'] = $item['region_id'];
                $children = $this->getRegionListForApp($where, $keys);
                //special compatible for app
                if ($item['level'] == 3 && 0 === count($children)) {
                    $children = [[
                        'region_id' => -1,
                        'name'      => '默认',
                    ]];
                }
                $data[$k]['children'] = $children;
            }
        }
        
        return $data;
    }
}
