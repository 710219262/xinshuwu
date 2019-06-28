<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 17:14
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ArticleCmtLike
 *
 * @package App\Models
 * @property User       $user
 * @property ArticleCmt $comment
 */
class ArticleCmtLike extends Model
{
    protected $table = 'xsw_article_comment_like';
    
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
            ArticleCmt::class,
            'comment_id',
            'id'
        );
    }
}
