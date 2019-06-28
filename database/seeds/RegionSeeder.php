<?php

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->initGlobalCountry();
        $this->initChinaRegion();
        $this->initTaiwanRegion();
        $this->initRegionName();
    }
    
    /**
     * 初始化全球国家数据
     */
    protected function initGlobalCountry()
    {
        $json = $this->parseJson(__DIR__ . '/../data/country.json');
        
        foreach ($json as $country) {
            $phone = substr($country['phone_code'], 1);
            /** @var Region $parentModel */
            Region::query()->create([
                'region_id'   => $phone,
                'name'        => $country['cn'],
                'region_name' => $country['cn'],
                'name_en'     => $country['en'],
                'is_foreign'  => $phone != '86',
            ]);
        }
    }
    
    /**
     * 初始化中国数据
     */
    protected function initChinaRegion()
    {
        $json = $this->parseJson(__DIR__ . '/../data/china-region.json');
        foreach ($json as $parentRegionId => $subRegionObj) {
            foreach ($subRegionObj as $regionId => $name) {
                /** @var Region $parentModel */
                
                $parentModel = Region::query()
                    ->where('region_id', $parentRegionId)
                    ->first();
                
                $level = empty($parentModel) ? 1 : $parentModel->level + 1;
                
                Region::query()->create([
                    'parent_id' => $parentRegionId,
                    'region_id' => $regionId,
                    'name'      => $name,
                    'level'     => $level,
                ]);
            }
        }
    }
    
    /**
     * 初始化台湾
     */
    protected function initTaiwanRegion()
    {
        $json = $this->parseJson(__DIR__ . '/../data/taiwan.json');
        $twId = '710000';
        
        foreach ($json as $city) {
            $parentRegionId = $city['city_id'];
            
            Region::query()->create([
                'parent_id' => $twId,
                'region_id' => $city['city_id'],
                'name'      => $city['city'],
                'level'     => 3,
            ]);
            
            if (count($city['areas']) > 0) {
                foreach ($city['areas'] as $area) {
                    /** @var Region $parentModel */
                    
                    $parentModel = Region::query()
                        ->where('region_id', $parentRegionId)
                        ->first();
                    
                    $level = empty($parentModel) ? 1 : $parentModel->level + 1;
                    
                    Region::query()->create([
                        'parent_id' => $parentRegionId,
                        'region_id' => $area['area_id'],
                        'name'      => $area['area'],
                        'level'     => $level,
                    ]);
                }
            }
        }
    }
    
    /**
     * 初始化地区名
     */
    protected function initRegionName()
    {
        $regions = Region::query()
            ->where('region_name', '=', '')
            ->get();
        
        foreach ($regions as $region) {
            /** @var Region $region */
            $parentNames = $this->findParents($region);
            $parentNames[] = $region->name;
            
            $region->update([
                'region_name' => implode(' ', $parentNames),
            ]);
        }
    }
    
    /**
     * @param Region $region
     * @return array
     */
    protected function findParents(Region $region): array
    {
        $parentNames = [];
        
        while ($parent = Region::query()
            ->where('region_id', $region->parent_id)
            ->first()) {
            
            /** @var Region $parent */
            
            if ($parent->region_name != '') {
                $parentNames[] = $parent->region_name;
                return $parentNames;
            }
            
            $parentNames[] = $parent->name;
            $region = $parent;
        }
        
        return $parentNames;
    }
    
    protected function parseJson($file)
    {
        $data = file_get_contents($file);
        return json_decode($data, true);
    }
}
