<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 24/04/2019
 * Time: 23:27
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserShare
 *
 * @package App\Models
 * @property integer $id
 * @property integer $user_id
 * @property integer $goods_id
 * @property integer $store_id
 * @property integer $target_id
 * @property string  $target
 * @property string  $aff
 * @property integer $view
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property User    $userRlt
 */
class UserShare extends Model
{
    protected $table = 'xsw_user_share';
    protected $guarded = ['id'];
    
    const T_EXP = 'EXP';
    const T_ARTICLE = 'ARTICLE';
    const T_GOODS = 'GOODS';
    
    protected $casts = [
        'goods_id'     => 'integer',
        'user_id'      => 'integer',
        'income'       => 'double',
        'total_income' => 'double',
    ];
    
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
        return $this->belongsTo(
            GoodsInfo::class,
            'goods_id',
            'id'
        );
    }

    public function targetExp()
    {
        return $this->belongsTo(
            UserExp::class,
            'target_id',
            'id'
        );
    }

    public function targetArticle()
    {
        return $this->belongsTo(
            Article::class,
            'target_id',
            'id'
        );
    }
    
    public static function buildLink($aff)
    {
        return sprintf(
            "%s%s",
            config('xsw.share.jump_prefix'),
            $aff
        );
    }
    
    /**
     * @param $target
     * @param $targetId
     * @param $userId
     *
     * @return string
     */
    public static function generateAff($target, $targetId, $userId)
    {
        $aff = self::newAff($target, $targetId, $userId);
        while (self::query()->where('aff', $aff)->count() > 0) {
            $aff = self::newAff($target, $targetId, $userId);
        }
        return $aff;
    }
    
    /**
     * @param $target
     * @param $targetId
     * @param $userId
     *
     * @return string
     */
    public static function newAff($target, $targetId, $userId)
    {
        return base64_encode(md5(sprintf("%s|%s|%s", $target, $targetId, $userId)) . time());
    }
}
