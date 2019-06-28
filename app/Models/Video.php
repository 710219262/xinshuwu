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
class Video extends Model
{
    use SoftDeletes;
    
    protected $table = 'xsw_video';
    
    protected $guarded = ['id'];
    
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    /**
     * Article constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public function getAuthorAttribute()
    {
        return $this->belongsTo(
            Admin::class,
            'author_id',
            'id'
        )->get([
            'id',
            'name',
            'phone',
        ]);
        return new \stdClass();
    }
}
