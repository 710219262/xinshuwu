<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 08/03/2019
 * Time: 12:10
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GoodsInfo
 *
 * @package App\Models
 * @property integer         $id
 * @property integer         $store_id
 * @property integer         $category_id
 * @property string          $name
 * @property string          $type
 * @property integer         $status
 * @property array           $sku_values
 * @property double          $price
 * @property Carbon          $deleted_at
 * @property Carbon          $created_at
 * @property Carbon          $updated_at
 * @property array           $info_imgs
 * @property array           $banner_imgs
 * @property MerchantAccount $store
 */
class GoodsInfo extends Model
{
    use SoftDeletes;
    
    const T_COMMON = 'COMMON';
    const T_IMPORT = 'IMPORT';
    
    protected $table = 'xsw_goods_info';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'category_id'  => 'int',
        'sku_values'   => 'json',
        'price'        => 'double',
        'market_price' => 'double',
    ];
    
    protected $appends = ['banner_imgs'];
    
    const S_ON_SALE = 'ON_SALE';
    const S_DRAFT = 'DRAFT';
    const S_SOLDOUT = 'SOLDOUT';

    /**
     * Article constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        /** @var Request $request */
        $request = app('request');

        // do not append attribute for mms
        if (!strstr($request->url(), "mms")) {
            $this->append(['is_collected']);
        };
    }
    
    public function store()
    {
        return $this->belongsTo(MerchantAccount::class, 'store_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(MerchantInfo::class, 'store_id', 'account_id');
    }

    public function skuRlt()
    {
        return $this->hasMany(GoodsSku::class, 'goods_id', 'id');
    }
    
    public function bannerImgsRlt()
    {
        return $this->hasMany(GoodsImg::class, 'goods_id', 'id')
            ->where('type', GoodsImg::T_BANNER);
    }
    
    public function infoImgsRlt()
    {
        return $this->hasMany(GoodsImg::class, 'goods_id', 'id')
            ->where('type', GoodsImg::T_INFO)
            ->select(['goods_id', 'url']);
    }
    
    public function getInfoImgsAttribute()
    {
        return $this->infoImgsRlt()->pluck('url');
    }
    
    public function getBannerImgsAttribute()
    {
        return $this->bannerImgsRlt()->pluck('url');
    }
    
    public function shareRlt()
    {
        return $this->hasMany(
            UserShare::class,
            'target_id',
            'id'
        )->where('target', UserShare::T_GOODS);
    }

    public function collectionRlt()
    {
        return $this->belongsTo(UserCollection::class, 'id', 'collect_id');
    }
    
    public function getShareUsersAttribute()
    {
        $builder = $this->shareRlt()->select([
            'id',
            'user_id',
        ]);
        
        $countBuilder = clone $builder;
        
        $userIds = $builder->limit(3)->pluck('user_id')->toArray();
        
        return [
            'count' => $countBuilder->count(),
            'users' => User::query()
                ->whereIn('id', $userIds)
                ->select('avatar')->pluck('avatar')->toArray(),
        ];
    }
    
    public function getSkuAttribute()
    {
        return $this->skuRlt()->select([
            'id',
            'has_spec',
            'price',
            'market_price',
            'inventory',
            'sku_name',
        ])->get();
    }

    public function getIsCollectedAttribute()
    {
        if (!app('request')->user()) {
            return false;
        }

        $isCollected = app('request')->user()->collectionRlt()
            ->where('type', '=', UserCollection::T_GOODS)
            ->where('collect_id', '=', $this->id)
            ->exists();

        return $isCollected;
    }
}
