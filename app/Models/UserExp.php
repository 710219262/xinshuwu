<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 22/03/2019
 * Time: 12:22
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserExp
 *
 * @package App\Models
 * @property integer   $id
 * @property integer   $user_id
 * @property integer   $goods_id
 * @property string    $content
 * @property string    $title
 * @property integer   $view
 * @property integer   $like
 * @property Carbon    $created_at
 * @property Carbon    $updated_at
 * @property Carbon    $deleted_at
 * @property GoodsInfo $goods
 */
class UserExp extends Model
{
    use SoftDeletes;
    protected $table = 'xsw_user_exp';
    
    protected $guarded = ['id'];
    
    protected $appends = ['media'];
    
    protected $casts = [
        'view'         => 'integer',
        'like'         => 'integer',
        'collect'      => 'integer',
        'user_id'      => 'integer',
        'share'        => 'integer',
        'income'       => 'double',
        'total_income' => 'double',
    ];
    
    // 状态
    const S_CREATED = 'CREATED';
    const S_REJECTED = 'REJECTED';
    const S_COMPLETED = 'COMPLETED';
    
    const STATUS_MAPPING = [
        self::S_CREATED   => '待审核',
        self::S_REJECTED  => '已拒绝',
        self::S_COMPLETED => '已完成',
    ];
    
    /**
     * UserExp constructor.
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
            $this->append([
                'is_liked',
                'share_users',
                'is_collected',
                'is_followed',
            ]);
        };
    }
    
    public function commentRlt()
    {
        return $this->hasMany(
            UserExpCmt::class,
            'exp_id',
            'id'
        );
    }
    
    public function mediaRlt()
    {
        return $this->hasMany(
            UserExpMedia::class,
            'exp_id',
            'id'
        );
    }
    
    public function shareRlt()
    {
        return $this->hasMany(
            UserShare::class,
            'target_id',
            'id'
        )->where('target', UserShare::T_EXP);
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
    
    public function comments()
    {
        return $this->commentRlt()->with([
            'user' => function ($q) {
                /** @var Builder $q */
                $q->select(['id', 'avatar', 'nickname']);
            },
        ])->select([
            'id',
            'pid',
            'content',
            'like',
            'is_author',
            'user_id',
            'created_at',
        ])->where('pid', '=', 0)->get();
    }
    
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }
    
    public function goods()
    {
        return $this->hasOne(
            GoodsInfo::class,
            'id',
            'goods_id'
        );
    }
    
    public function getMediaAttribute()
    {
        return $this->mediaRlt()->select([
            'url',
            'type',
            'height',
            'width',
        ])->get();
    }
    
    public function getIsLikedAttribute()
    {
        //maybe just a tourist
        $user = app('request')->user();
        
        if (empty($user)) {
            return false;
        }
        
        $isLiked = UserExpLike::query()
            ->where('exp_id', $this->id)
            ->where('user_id', $user->id)
            ->exists();
        
        return $isLiked;
    }
    
    public function getIsCollectedAttribute()
    {
        if (!app('request')->user()) {
            return false;
        }
        $isCollected = app('request')->user()->collectionRlt()
            ->where('type', '=', UserCollection::T_EXP)
            ->where('collect_id', '=', $this->id)
            ->exists();
        
        return $isCollected;
    }
    
    public function getIsFollowedAttribute()
    {
        $user = app('request')->user();
        
        if (empty($user)) {
            return false;
        }
        
        $isFollowed = UserFollow::query()
            ->where('follower_id', '=', $user->id)
            ->where('followed_id', '=', $this->user_id)
            ->exists();
        
        return $isFollowed;
    }
}
