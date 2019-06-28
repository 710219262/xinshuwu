<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/03/2019
 * Time: 23:45
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserShoppingCart
 *
 * @property integer   $id
 * @property integer   $user_id
 * @property integer   $goods_id
 * @property integer   $sku_id
 * @property integer   $count
 * @property GoodsInfo $goods
 * @property GoodsSku  $sku
 * @property string    $goods_name
 * @property string    $sku_name
 * @package App\Models
 */
class UserShoppingCart extends Model
{
    use SoftDeletes;
    
    protected $table = 'xsw_user_shopping_cart';
    
    protected $guarded = ['id'];
    
    protected $appends = ['goods_name', 'sku_name', 'price', 'banner_imgs'];
    
    protected $casts = [
        'count' => 'int',
    ];
    
    // 正常
    const S_NORMAL = 'NORMAL';
    // 失效
    const S_INVALID = 'INVALID';
    // 售罄
    const S_SOLDOUT = 'SOLDOUT';
    
    public function goods()
    {
        return $this->hasOne(
            GoodsInfo::class,
            'id',
            'goods_id'
        )->withTrashed();
    }
    
    public function sku()
    {
        return $this->hasOne(
            GoodsSku::class,
            'id',
            'sku_id'
        )->withTrashed();
    }
    
    public function getGoodsNameAttribute()
    {
        return $this->goods->name;
    }
    
    public function getSkuNameAttribute()
    {
        return $this->sku->sku_name;
    }
    
    public function getPriceAttribute()
    {
        return $this->sku->price;
    }
    
    public function getBannerImgsAttribute()
    {
        return $this->goods->banner_imgs;
    }

    public function getStatusAttribute()
    {
        $sku = $this->sku;
        $goods = $this->goods;

        if ($sku->deleted_at || $goods->deleted_at) {
            return 'INVALID';
        }

        return [
            'ON_SALE' => 'NORMAL',
            'DRAFT'   => 'INVALID',
            'SOLDOUT' => 'SOLDOUT',
        ][$goods->status];
    }
}
