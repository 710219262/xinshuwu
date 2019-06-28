<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 17:14
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleLike extends Model
{
    protected $table = 'xsw_article_like';
    
    protected $guarded = ['id'];
    
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }
    
    public function article()
    {
        return $this->belongsTo(
            Article::class,
            'article_id',
            'id'
        );
    }
}
