<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/03/2019
 * Time: 22:30
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserCollection
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $collect_id
 * @property User    $user
 *
 *
 * @package App\Models
 */
class UserCollection extends Model
{
    use SoftDeletes;
    protected $table = 'xsw_user_collection';
    
    protected $guarded = ['id'];
    
    const T_ARTICLE = 'ARTICLE';
    const T_EXP = 'EXP';
    const T_GOODS = 'GOODS';
    const T_STORE = 'STORE';
    
    const T_TABLE_MAP = [
        self::T_ARTICLE => 'xsw_article',
        self::T_EXP     => 'xsw_user_exp',
        self::T_GOODS   => 'xsw_goods_info',
        self::T_STORE   => 'xsw_merchant_account',
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
        return $this->hasOne(GoodsInfo::class, 'id', 'collect_id');
    }
    
    public function store()
    {
        return $this->hasOne(MerchantAccount::class, 'id', 'collect_id');
    }
    
    public function article()
    {
        return $this->hasOne(Article::class, 'id', 'collect_id');
    }
    
    public function experience()
    {
        return $this->hasOne(UserExp::class, 'id', 'collect_id');
    }
}
