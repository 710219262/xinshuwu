<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 16/04/2019
 * Time: 21:44
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Notification
 *
 * @package App\Models
 * @property integer $id
 * @property integer $a_user_id
 * @property integer $r_user_id
 * @property integer $refer_id
 * @property string  $jump
 * @property string  $action
 * @property string  $target
 * @property boolean $is_read
 * @property string  $payload
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class Notification extends Model
{
    protected $table = 'xsw_user_notification';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'a_user_id' => 'integer',
        'r_user_id' => 'integer',
        'refer_id'  => 'integer',
        'payload'   => 'array',
        'is_read'   => 'boolean',
    ];
    
    const LIST_TO_ACTION = [
        'LIKE_CLT' => [
            self::A_LIKE,
            self::A_COLLECT,
        ],
        'FLW'      => [
            self::A_FOLLOW,
        ],
        'CMT'      => [
            self::A_COMMENT,
            self::A_REPLY,
        ],
    ];
    
    const A_LIKE = 'LIKE';
    
    const A_COLLECT = 'CLT';
    const A_COMMENT = 'CMT';
    const A_REPLY = 'RPL';
    const A_FOLLOW = 'FLW';
    
    const J_EXP = 'EXP';
    const J_ARTICLE = 'ARTICLE';
    const J_USER = 'USER';
    
    const T_EXP = 'EXP';
    const T_EXP_COMMENT = 'EXP_CMT';
    const T_ARTICLE = 'ARTICLE';
    const T_SHOP = 'SHOP';
    const T_USER = 'USER';
}
