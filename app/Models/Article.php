<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 14:40
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

/**
 * Class Article
 *
 * @package App\Models
 * @property integer       $id
 * @property integer       $category_id
 * @property integer       $goods_id
 * @property integer       $author_id
 * @property integer       $publisher
 * @property string        $cover
 * @property string        $title
 * @property string        $content
 * @property string        $status
 * @property Carbon        $published_at
 * @property integer       $collect
 * @property integer       $like
 * @property integer       $share
 * @property integer       $view
 * @property integer       $sale
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 * @property GoodsCategory $category
 * @property GoodsInfo     $goods
 */
class Article extends Model
{
    use SoftDeletes;
    
    protected $table = 'xsw_article';
    
    protected $guarded = ['id'];
    
    protected $dates = [
        'created_at',
        'updated_at',
        'published_at',
    ];
    
    protected $casts = [
        'category_id' => 'integer',
        'collect'     => 'integer',
        'like'        => 'integer',
        'share'       => 'integer',
        'view'        => 'integer',
    ];
    
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
            $this->append([
                'is_liked',
                'share_users',
                'is_collected',
                'is_followed',
            ]);
        };
    }
    
    const S_PUBLISHED = 'PUBLISHED';
    const S_AUDIT_PENDING = 'AUDIT_PENDING';
    const S_REJECTED = 'REJECTED';
    const S_OFFLINE = 'OFFLINE';
    const S_DRAFT = 'DRAFT';
    
    const P_MERCHANT = 'MERCHANT';
    const P_USER = 'USER';
    const P_PLATFORM = 'PLATFORM';
    
    public function category()
    {
        return $this->belongsTo(
            GoodsCategory::class,
            'category_id',
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
    
    public function commentRlt()
    {
        return $this->hasMany(
            ArticleCmt::class,
            'article_id',
            'id'
        );
    }
    
    public function shareRlt()
    {
        return $this->hasMany(
            UserShare::class,
            'target_id',
            'id'
        )->where('target', UserShare::T_ARTICLE);
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
    
    public function getIsLikedAttribute()
    {
        //maybe just a tourist
        $user = app('request')->user();
        
        if (empty($user)) {
            return false;
        }
        
        $isLiked = ArticleLike::query()
            ->where('article_id', $this->id)
            ->where('user_id', $user->id)
            ->exists();
        
        return $isLiked;
    }
    
    public function getAuthorAttribute()
    {
        if ($this->publisher === self::P_MERCHANT) {
            return $this->belongsTo(
                MerchantAccount::class,
                'author_id',
                'id'
            )->get([
                'id',
                'name',
                'logo',
                'desc',
                'status',
            ]);
        }
        return new \stdClass();
    }
    
    public function getIsCollectedAttribute()
    {
        if (!app('request')->user()) {
            return false;
        }
        
        $isCollected = app('request')->user()->collectionRlt()
            ->where('type', '=', UserCollection::T_ARTICLE)
            ->where('collect_id', '=', $this->id)
            ->exists();
        
        return $isCollected;
    }
    
    public function getIsFollowedAttribute()
    {
        if (!app('request')->user()) {
            return false;
        }
        
        $isCollected = app('request')->user()->collectionRlt()
            ->where('type', '=', UserCollection::T_STORE)
            ->where('collect_id', '=', $this->author_id)
            ->exists();
        
        return $isCollected;
    }
}
