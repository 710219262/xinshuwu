<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 06/03/2019
 * Time: 15:57
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MerchantAccount
 *
 * @package App\Models
 * @property integer      $id
 * @property integer      $no
 * @property string       $phone
 * @property string       $name
 * @property integer      $category_id
 * @property string       $logo
 * @property string       $desc
 * @property integer      $status
 * @property integer      $collect
 * @property boolean      $is_collected
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 * @property MerchantInfo $merchantInfo
 */
class MerchantAccount extends Model
{
    const ROLE = 'merchant';
    const S_REJECTED = -2;
    const S_UNCHECKED = -1;
    const S_CHECKING = 0;
    const S_CHECKED = 1;
    
    protected $table = 'xsw_merchant_account';
    
    protected $guarded = ['id'];

    protected $casts = [
        'collect'  => 'integer',
    ];

    /**
     * MerchantAccount constructor.
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function merchantInfo()
    {
        return $this->hasOne(MerchantInfo::class, 'account_id', 'id');
    }
    
    public function orderRlt()
    {
        return $this->hasMany(Order::class, 'store_id', 'id');
    }

    public function collectionRlt()
    {
        return $this->belongsTo(UserCollection::class, 'id', 'collect_id');
    }
    
    /**
     * @param $query
     *
     * @return Collection
     */
    public function orders($query, $offset = 0, $pageSize = 0)
    {
        $builder = $this->orderRlt();
        
        if ($orderNo = array_get($query, 'order_no')) {
            $builder->where('order_no', $orderNo);
        }
        
        if ($begin = array_get($query, 'begin')) {
            $builder->where('created_at', '>=', $begin);
        }
        
        if ($end = array_get($query, 'end')) {
            $builder->where('created_at', '<=', $end);
        }
        
        if ($method = array_get($query, 'logistic_abbr')) {
            $builder->where('logistic_abbr', $method);
        }
        
        if ($method = array_get($query, 'pay_method')) {
            $builder->where('pay_method', $method);
        }
        
        if ($status = array_get($query, 'status')) {
            $builder->where('status', $status);
        }
        
        if ($goodsName = array_get($query, 'goods_name')) {
            // hack method
            $builder->whereHas('goods', function ($q) use ($goodsName) {
                /** @var Builder $q */
                $q->whereRaw(
                    \DB::raw("JSON_EXTRACT(`snapshot`, \"$.goods_info.name\") LIKE '%" . $goodsName . "%'")
                );
            });
        }

        $Total = $builder->count();

        //暂时考虑兼容性
        if(!empty($pageSize)) {
            $builder->offset($offset)->limit($pageSize);
        }

        $orders = $builder->select([
            'batch_no',
            'order_no',
            'store_id',
            'total_amount',
            'pay_amount',
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
            'is_test'
        ])->with([
            'goods',
        ])->orderBy('id', 'desc')
            ->get();

        //暂时考虑兼容性
        if(empty($pageSize)) {
            return $orders;
        }
        return ['total'=>$Total,'list'=>$orders];
    }

    public function getIsCollectedAttribute()
    {
        if (!app('request')->user()) {
            return false;
        }

        $isCollected = app('request')->user()->collectionRlt()
            ->where('type', '=', UserCollection::T_STORE)
            ->where('collect_id', '=', $this->id)
            ->exists();

        return $isCollected;
    }
}
