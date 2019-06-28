<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 22/03/2019
 * Time: 12:24
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserExpCmtLike
 *
 * @package App\Models
 * @property User       $user
 * @property UserExpCmt $comment
 */
class UserExpCmtLike extends Model
{
    protected $table = 'xsw_user_exp_comment_like';
    
    protected $guarded = ['id'];
    
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }
    
    public function comment()
    {
        return $this->belongsTo(
            UserExpCmt::class,
            'comment_id',
            'id'
        );
    }
}
