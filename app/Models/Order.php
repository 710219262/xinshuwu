<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 29/03/2019
 * Time: 14:18
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class Order
 *
 * @package App\Models
 * @property integer    $id
 * @property integer    $order_no
 * @property integer    $batch_no
 * @property integer    $refund_no
 * @property integer    $user_id
 * @property integer    $store_id
 * @property string     $status
 * @property double     $total_amount
 * @property double     $pay_amount
 * @property double     $goods_price
 * @property integer    $delivery_price
 * @property integer    $order_detail
 * @property integer    $pay_method
 * @property integer    $address_id
 * @property array      $address
 * @property string     $shipping_address
 * @property string     $logistic_no
 * @property string     $logistic_info
 * @property string     $logistic_abbr
 * @property string     $logistic_company
 * @property string     $aff
 * @property Collection $goods
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 */
class Order extends Model
{
    protected $table = 'xsw_user_order';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'total_amount'    => 'double',
        'pay_amount'      => 'double',
        'goods_price'     => 'double',
        'delivery_price'  => 'double',
        'discount_amount' => 'double',
        'payload'         => 'array',
        'is_deleted'      => 'boolean',
        'address'         => 'array',
        'logistic_info'   => 'array',
    ];
    
    // 订单状态
    const S_CREATED = 'CREATED';
    const S_PAYED = 'PAYED';
    const S_SHIPPED = 'SHIPPED';
    const S_RECEIVED = 'RECEIVED';
    const S_COMPLETED = 'COMPLETED';
    const S_SHARED = 'SHARED';
    const S_RETURNING = 'RETURNING';
    const S_RETURNED = 'RETURNED';
    const S_CANCELED = 'CANCELED';
    
    const STATUS_MAPPING = [
        self::S_CREATED   => '待付款',
        self::S_PAYED     => '待发货',
        self::S_SHIPPED   => '待收货',
        self::S_RECEIVED  => '待分享',
        self::S_SHARED    => '已分享',
        self::S_COMPLETED => '已完成',
        self::S_RETURNING => '退货中',
        self::S_RETURNED  => '已退货',
        self::S_CANCELED  => '已取消',
    ];
    
    // 支付方式
    const P_ALI = 'ALIPAY';
    const P_WECHAT = 'WECHAT';
    const P_WECHAT_JSAPI = 'WECHAT_JSAPI';

    const DEFAULT_BODY = '猩事物订单支付';
    
    const LOGISTIC_QUERY_GAP_IN_HOUR = 3;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(MerchantAccount::class, 'store_id', 'id');
    }
    
    public function goods()
    {
        return $this->hasMany(OrderGoods::class, 'order_no', 'order_no');
    }
    
    /**
     * @param $phone
     *
     * @return string
     */
    public static function newBatchNum($phone)
    {
        $batchNo = self::generateNum($phone);
        while (Order::query()->where('batch_no', $batchNo)->lockForUpdate()->count() > 0) {
            $batchNo = Order::generateNum($phone);
        }
        return $batchNo;
    }
    
    /**
     * @param $phone
     *
     * @return string
     */
    public static function newOrderNum($phone)
    {
        $orderNo = self::generateNum($phone);
        while (Order::query()->where('order_no', $orderNo)->lockForUpdate()->count() > 0) {
            $orderNo = Order::generateNum($phone);
        }
        return $orderNo;
    }
    
    /**
     * @param $phone
     *
     * @return string
     */
    protected static function generateNum($phone)
    {
        return sprintf(
            "%s%s%s",
            time(),
            substr($phone, -4),
            rand(10, 99)
        );
    }
}
