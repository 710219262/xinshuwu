<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 18/03/2019
 * Time: 22:11
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class User
 *
 * @package App\Models
 * @property integer $id
 * @property string  $phone
 * @property string  $qq_uid
 * @property string  $wb_uid
 * @property string  $wechat_uid
 * @property string  $avatar
 * @property string  $nickname
 * @property string  $motto
 * @property string  $gender
 * @property string  $birthday
 * @property integer $fans_count
 * @property integer $follow_count
 * @property integer $favorite_count
 * @property integer $liked_count
 * @property integer $xb
 * @property bool    $vip
 * @property bool    $is_vip
 * @property Carbon  $vip_card
 * @property integer $status
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class User extends Model
{
    protected $table = 'xsw_user';
    
    protected $guarded = ['id'];
    
    protected $appends = ['is_vip'];
    
    protected $casts = [
        'vip'            => 'boolean',
        'fans_count'     => 'integer',
        'follow_count'   => 'integer',
        'favorite_count' => 'integer',
        'liked_count'    => 'integer',
    ];
    
    protected $dates = [
        'crated_at',
        'updated_at',
    ];
    
    const L_QQ = 'qq';
    const L_WECHAT = 'wechat';
    const L_WB = 'weibo';
    const L_PHONE = 'phone';
    
    const L_ID_MAP = [
        self::L_QQ     => 'qq_uid',
        self::L_WECHAT => 'wechat_uid',
        self::L_WB     => 'wb_uid',
        self::L_PHONE  => 'phone',
    ];
    
    /**
     * @return bool
     */
    public function isVip()
    {
        return $this->is_vip;
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function followedRlt()
    {
        return $this->hasManyThrough(
            User::class,
            UserFollow::class,
            'follower_id',
            'id',
            'id',
            'followed_id'
        );
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function followerRlt()
    {
        return $this->hasManyThrough(
            User::class,
            UserFollow::class,
            'followed_id',
            'id',
            'id',
            'follower_id'
        );
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userFollowRlt()
    {
        return $this->hasMany(
            UserFollow::class,
            'follower_id',
            'id'
        );
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function collectionRlt()
    {
        return $this->hasMany(
            UserCollection::class,
            'user_id',
            'id'
        );
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addressRlt()
    {
        return $this->hasMany(
            UserAddress::class,
            'user_id',
            'id'
        );
    }
    
    public function shoppingCartRlt()
    {
        return $this->hasMany(
            UserShoppingCart::class,
            'user_id',
            'id'
        );
    }
    
    public function orderRlt()
    {
        return $this->hasMany(
            Order::class,
            'user_id',
            'id'
        );
    }
    
    public function tagRlt()
    {
        return $this->hasMany(
            UserTag::class,
            'user_id',
            'id'
        );
    }
    
    public function getIsVipAttribute()
    {
        if ($this->vip_card) {
            return Carbon::createFromTimeString($this->vip_card)->gt(Carbon::now());
        }
        return false;
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shoppingCart()
    {
        $cart = $this->shoppingCartRlt()->orderBy('id', 'DESC')
            ->where('status', '=', UserShoppingCart::S_NORMAL)
            ->select([
                'id',
                'goods_id',
                'sku_id',
                'count',
                'status',
            ])->get();
        
        $cart->transform(function ($i) {
            /** @var \Illuminate\Support\Collection $i */
            return array_except($i->toArray(), [
                'goods',
                'sku',
            ]);
        });
        
        return $cart;
    }
    
    /**
     * @param $status
     *
     * @return Collection
     */
    public function orders($status)
    {
        $builder = $this->orderRlt();
        
        if (!empty($status)) {
            $builder->where('status', $status);
        }
        
        $builder->where('is_deleted', '=', false)->orderBy('id', 'DESC');
        
        $orders = $builder->select([
            'batch_no',
            'order_no',
            'store_id',
            'total_amount',
            'pay_amount',
            \DB::raw("(total_amount - pay_amount) as discount_amount"),
            'goods_price',
            'delivery_price',
            'pay_method',
            'address',
            'status',
            'logistic_no',
            'logistic_company',
            'logistic_info',
            'created_at',
            'updated_at',
        ])->with([
            'goods' => function (HasMany $q) {
                $q->with([
                    'aftersale' => function (HasOne $q) {
                        $q->select([
                            'aftersale_no',
                            'order_goods_id',
                            'status'
                        ]);
                    }
                ]);
            },
            'store' => function ($q) {
                /** @var \Illuminate\Database\Query\Builder $q */
                $q->select(['id', 'name', 'phone']);
            },
        ])->get();
        
        return $orders;
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function address()
    {
        return $this->addressRlt()->select([
            'xsw_user_address.id',
            'contact',
            'phone',
            'address',
            'number',
            'region',
            'tag',
            'is_default',
            'province_id as pid',
            'city_id as cid',
            'area_id as aid',
        ]);
    }
    
    /**
     * 关注列表
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function followed()
    {
        return $this->followedRlt()
            ->select([
                'xsw_user.id',
                'nickname',
                'avatar',
            ]);
    }
    
    /**
     * 粉丝列表
     *
     * @return  array
     */
    public function follower()
    {
        $follower = $this->followerRlt()->select([
            'xsw_user.id',
            'nickname',
            'avatar',
        ])->get()->toArray();
        
        foreach ($follower as $i => $f) {
            $follower[$i]['is_followed'] = UserFollow::query()
                ->where('followed_id', $f['id'])
                ->where('follower_id', $this->id)
                ->exists();
        }
        
        return $follower;
    }
    
    /**
     * 商品收藏列表
     *
     * @return array
     */
    public function goodsCollections()
    {
        $collections = $this->collectionRlt()
            ->where('type', '=', UserCollection::T_GOODS)
            ->with([
                'goods' => function (HasOne $q) {
                    $q->select([
                        'id',
                        'name',
                        'status',
                        'price',
                        'market_price',
                        'collect',
                    ]);
                },
            ])->select([
                'id',
                'collect_id',
                'type',
            ])->get()->toArray();
        return $collections;
    }
    
    /**
     * 店铺收藏列表
     *
     * @return array
     */
    public function storeCollections()
    {
        $collections = $this->collectionRlt()
            ->where('type', '=', UserCollection::T_STORE)
            ->with([
                'store' => function (HasOne $q) {
                    $q->select([
                        'id',
                        'name',
                        'logo',
                        'desc',
                        'collect',
                    ]);
                },
            ])->select([
                'id',
                'collect_id',
                'type',
            ])->get()->toArray();
        
        return $collections;
    }
    
    public function articleCollections()
    {
        $collections = $this->collectionRlt()
            ->where('type', '=', UserCollection::T_ARTICLE)
            ->with([
                'article' => function (HasOne $q) {
                    $q->select([
                        'id',
                        'title',
                        'cover',
                        'goods_id',
                        'collect',
                    ])->with([
                        'goods' => function (HasOne $q) {
                            $q->select([
                                'id',
                                'name',
                                'price',
                                'market_price',
                            ]);
                        },
                    ]);
                },
            ])->select([
                'id',
                'collect_id',
                'type',
            ])->get()->toArray();
        
        return $collections;
    }
    
    public function expCollections()
    {
        $collections = $this->collectionRlt()
            ->where('type', '=', UserCollection::T_EXP)
            ->with([
                'experience' => function (HasOne $q) {
                    $q->select([
                        'id',
                        'title',
                        'goods_id',
                        'collect',
                    ])->with([
                        'goods' => function (HasOne $q) {
                            $q->select([
                                'id',
                                'name',
                                'price',
                                'market_price',
                            ]);
                        },
                    ]);
                },
            ])->select([
                'id',
                'collect_id',
                'type',
            ])->get()->toArray();
        
        return $collections;
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shoppingCartByIds(array $cat_ids = [])
    {
        $cart = $this->shoppingCartRlt()->where('status', '=', UserShoppingCart::S_NORMAL)
            ->whereIn('id', $cat_ids)->select([
                'id',
                'goods_id',
                'sku_id',
                'count',
            ])->get();
        
        $cart->transform(function ($i) {
            /** @var \Illuminate\Support\Collection $i */
            return array_except($i->toArray(), [
                'goods',
                'sku',
            ]);
        });
        
        return $cart;
    }
}
