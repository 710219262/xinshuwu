<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/7
 * Time: 11:15
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class VipOrder
 *
 * @package App\Models
 * @property integer $id
 * @property integer $order_no
 * @property integer $user_id
 * @property string  $type
 * @property string  $status
 * @property double  $price
 * @property double  $pay_amount
 * @property integer $pay_method
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class UserVipOrder extends Model
{
    protected $table = 'xsw_user_vip_order';

    protected $guarded = ['id'];

    // 订单状态
    const S_CREATED = 'CREATED';
    const S_PAYED = 'PAYED';
    const S_CANCELED = 'CANCELED';

    const T_MONTH = 'MONTH';
    const T_SEASON = 'SEASON';
    const T_YEAR = 'YEAR';

    const STATUS_MAPPING = [
        self::S_CREATED   => '待付款',
        self::S_PAYED     => '已支付',
        self::S_CANCELED  => '已取消',
    ];

    // 支付方式
    const P_ALI = 'ALIPAY';
    const P_WECHAT = 'WECHAT';
    const P_WECHAT_JSAPI = 'WECHAT_JSAPI';

    const DEFAULT_BODY = '猩事物VIP会员购买';
    /**
     * @param $phone
     *
     * @return string
     */
    public static function newOrderNum($phone)
    {
        $orderNo = self::generateNum($phone);
        while (UserVipOrder::query()->where('order_no', $orderNo)->lockForUpdate()->count() > 0) {
            $orderNo = UserVipOrder::generateNum($phone);
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

    public static function getCardPriceByType($type)
    {
        switch ($type) {
            case UserVipOrder::T_MONTH:
                return config('xsw.vip_member.month_card_price');
                break;
            case UserVipOrder::T_SEASON:
                return config('xsw.vip_member.season_card_price');
                break;
            case UserVipOrder::T_YEAR:
                return config('xsw.vip_member.year_card_price');
                break;
            default:
                break;
        }
    }
}