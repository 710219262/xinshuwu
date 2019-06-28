<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 13/04/2019
 * Time: 22:05
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderGoods
 *
 * @package App\Models
 * @property integer $id
 * @property integer $user_id
 * @property integer $goods_id
 * @property integer $sku_id
 * @property string  $order_no
 * @property string  $aff
 * @property array   $snapshot
 * @property integer $count
 * @property double  $total_amount
 * @property double  $pay_amount
 * @property double  $org_per_price
 * @property double  $pay_per_price
 * @property integer $status
 * @property Order   $order
 * @property AfterSaleOrder $aftersale
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property array   goods_info
 */
class OrderGoods extends Model
{
    protected $table = 'xsw_user_order_goods';
    
    protected $guarded = ['id'];
    
    protected $appends = ['goods_info'];
    
    protected $casts = [
        'goods_id'      => 'integer',
        'total_amount'  => 'double',
        'pay_amount'    => 'double',
        'org_per_price' => 'double',
        'pay_per_price' => 'double',
        'snapshot'      => 'array',
        'count'         => 'int',
    ];


    public function share()
    {
        return $this->hasOne(UserShare::class, 'aff', 'aff');
    }

    public function aftersale()
    {
        return $this->hasOne(AfterSaleOrder::class, 'order_goods_id', 'id')
            ->orderBy('id', 'DESC');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'order_no', 'order_no');
    }

    public function getGoodsInfoAttribute()
    {
        return [
            'id'            => $this->id,
            'goods_id'      => $this->goods_id,
            'name'          => $this->snapshot['goods_info']['name'],
            'banner_imgs'   => $this->snapshot['goods_info']['banner_imgs'],
            'info_imgs'     => $this->snapshot['goods_info']['info_imgs'],
            'price'         => $this->snapshot['sku']['price'],
            'sku_name'      => $this->snapshot['sku']['sku_name'],
            'count'         => $this->count,
            'total_amount'  => $this->total_amount,
            'pay_amount'    => $this->pay_amount,
            'org_per_price' => $this->org_per_price,
            'pay_per_price' => $this->pay_per_price,
        ];
    }
}
