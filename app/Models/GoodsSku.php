<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 08/03/2019
 * Time: 12:09
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class GoodsSku
 *
 * @package App\Models
 * @property integer $id
 * @property integer $goods_id
 * @property integer $has_spec
 * @property integer $inventory
 * @property double  $price
 * @property string  $sku_name
 * @property integer $sku_no
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property Carbon  $deleted_at
 */
class GoodsSku extends Model
{
    use SoftDeletes;
    
    protected $table = 'xsw_goods_sku';
    protected $guarded = ['id'];
    
    protected $casts = [
        'has_spec'     => 'boolean',
        'specs'        => 'array',
        'price'        => 'double',
        'market_price' => 'double',
        'inventory'    => 'int',
        'spec_values'  => 'array',
    ];

    public function goodsinfo()
    {
        return $this->belongsTo(GoodsInfo::class, 'goods_id', 'id');
    }

    protected $appends = ['specs', 'spec_values'];
    
    public function specGroupRlt()
    {
        return $this->hasMany(GoodsSpecGroup::class, 'sku_id', 'id');
    }
    
    public function getSpecsAttribute()
    {
        return $this->specGroupRlt()->get()->pluck('sv_id')->map(function ($i) {
            return intval($i);
        });
    }
    
    
    public function getSpecValuesAttribute()
    {
        return $this->specGroupRlt()->get()->pluck('sv');
    }
}
