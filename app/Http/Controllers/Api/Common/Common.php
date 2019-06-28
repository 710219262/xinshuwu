<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 07/04/2019
 * Time: 17:30
 */

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\ExpressCompany;
use App\Models\GoodsCategory;
use App\Repos\Common\RegionRepo;
use App\Services\OssService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class Common extends Controller
{
    const CHINA_REGION = 86;
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function upload(Request $request)
    {
        $this->validate($request, [
            'file' => 'required',
        ]);
        
        $file = $request->file('file');
        $mime = $file->getMimeType();
        $key = md5_file($file);
        
        try {
            OssService::upload($key, $file, ['Content-Type' => $mime]);
            $domain = config('aliyun.oss.upload_domain');
            return json_response([
                'key' => $key,
                'url' => "{$domain}{$key}",
            ]);
        } catch (\Exception $e) {
            return json_response([], '上传失败', 409);
        }
    }
    
    /**
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getCategories()
    {
        $categories = GoodsCategory::query()
            ->where('pid', '=', 0)
            ->select([
                'id',
                'name',
            ])
            ->get();
        
        return json_response($categories);
    }
    
    /**
     * 获取地区列表
     *
     * @param Request    $request
     * @param RegionRepo $repo
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getRegionList(Request $request, RegionRepo $repo)
    {
        $this->validate($request, [
            'pid' => 'integer|exists:xsw_region,id',
        ]);
        
        $where['parent_id'] = self::CHINA_REGION;
        $where['is_foreign'] = false;
        
        $regions = $repo->getRegionListForApp($where, [
            'region_id',
            'name',
            'name_en',
            'is_foreign',
            'level',
        ]);
        
        return json_response($regions);
    }
    
    
    /**
     * server scoped conf
     *
     * @return array
     */
    public function getServerConf()
    {
        $conf = [
            'cdn'      => config('aliyun.oss.upload_domain'),
            'discount' => config('xsw.vip_discount'),
        ];
        
        return json_response($conf);
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getLogisticCompany(Request $request)
    {
        $this->validate($request, [
            'keywords' => 'string',
        ]);
        
        $builder = ExpressCompany::query()
            ->where('status', ExpressCompany::ENABLE);
        
        if ($request->has('keywords')) {
            $keywords = trim($request->input('keywords'));
            $builder->where(function ($q) use ($keywords) {
                /** @var Builder $q */
                $q->where('name', 'LIKE', "%$keywords%")
                    ->orWhere('abbr', 'LIKE', "%$keywords%");
            });
        }
        
        return json_response($builder->get([
            'id',
            'name',
            'abbr',
        ]));
    }
}
