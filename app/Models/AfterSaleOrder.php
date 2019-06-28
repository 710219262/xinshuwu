<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/14
 * Time: 13:00
 */

namespace App\Models;

use App\Events\Order\AfterSaleWasUpdated;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AfterSaleOrder
 *
 * @package App\Models
 * @property integer $id
 * @property string $aftersale_no
 * @property integer $order_goods_id
 * @property integer $user_id
 * @property integer $store_id
 * @property string $status
 * @property double $type
 * @property double $refund_amount
 * @property string $address_name
 * @property string $address_phone
 * @property string $address
 * @property string $shipping_name
 * @property string $shipping_phone
 * @property string $shipping_address
 * @property string $note
 * @property string $reason
 * @property string $logistic_no
 * @property string $logistic_company
 * @property string $logistic_abbr
 * @property string $logistic_info
 * @property OrderGoods $orderGoods
 * @property Order $order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class AfterSaleOrder extends Model
{
    protected $table = 'xsw_aftersale_order';

    protected $guarded = ['id'];

    protected $casts = [
        'images' => 'array',
    ];

    const S_REQUEST = 'REQUEST';
    const S_REJECTED = 'REJECTED';
    const S_AGREED = 'AGREED';
    const S_SHIPPING = 'SHIPPING';
    const S_RECEIVED = 'RECEIVED';
    const S_PROCESSING = 'PROCESSING';
    const S_COMPLETED = 'COMPLETED';
    const S_CANCEL = 'CANCEL';

    // 退款类型
    const T_REFUND = 'REFUND';
    const T_RETURN_REFUND = 'RETURN_REFUND';

    const TYPE_MAPPING = [
        self::T_RETURN_REFUND => '退货退款',
        self::T_REFUND        => '退款',
    ];

    // 退款原因
    const R_INFORMATION_WRONG = 'INFORMATION_WRONG';
    const R_BOUGHT_WRONG = 'BOUGHT_WRONG';
    const R_NOT_NEED = 'NOT_NEED';
    const R_SOLDOUT = 'SOLDOUT';
    const R_OTHER = 'OTHER';

    const REASON_MAPPING = [
        self::R_INFORMATION_WRONG => '信息有误',
        self::R_BOUGHT_WRONG      => '买错了',
        self::R_NOT_NEED          => '不想要了',
        self::R_SOLDOUT           => '商品无货',
        self::R_OTHER             => '其他原因',
    ];


    public function orderGoods()
    {
        return $this->hasOne(OrderGoods::class, 'id', 'order_goods_id');
    }

    public function getReasonAttribute($reason)
    {
        return array_get(self::REASON_MAPPING, $reason);
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'order_no', 'order_no');
    }

    /**
     * @param $phone
     *
     * @return string
     */
    public static function newOrderNum($phone)
    {
        $orderNo = self::generateNum($phone);
        while (AfterSaleOrder::query()->where('aftersale_no', $orderNo)->count() > 0) {
            $orderNo = self::generateNum($phone);
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

    public static function refundSuccess($aftersaleNo)
    {
        /** @var AfterSaleOrder $order */
        $order = AfterSaleOrder::query()
            ->where('aftersale_no', $aftersaleNo)
            ->where('status', AfterSaleOrder::S_PROCESSING)
            ->first();

        if ($order) {
            $order->update([
                'status'    => AfterSaleOrder::S_COMPLETED,
                'refund_at' => Carbon::now(),
            ]);
            event(new AfterSaleWasUpdated($order));
        }
    }
}
