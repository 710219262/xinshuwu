<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 22/03/2019
 * Time: 12:23
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserExpCmt
 *
 * @package App\Models
 * @property integer    $id
 * @property integer    $pid
 * @property integer    $exp_id
 * @property integer    $user_id
 * @property integer    $like
 * @property boolean    $is_author
 * @property string     $content
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 * @property Carbon     $deleted_at
 * @property User       $user
 * @property UserExpCmt $parent
 * @property UserExp    $exp
 */
class UserExpCmt extends Model
{
    protected $table = 'xsw_user_exp_comment';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'is_author' => 'boolean',
        'like'      => 'integer',
        'user_id'   => 'integer',
        'pid'       => 'integer',
    ];
    
    public function childrenRlt()
    {
        return $this->hasMany(
            self::class,
            'pid',
            'id'
        );
    }
    
    public function children()
    {
        return $this->childrenRlt()
            ->with([
                'user' => function ($q) {
                    /** @var Builder $q */
                    $q->select(['id', 'avatar', 'nickname']);
                },
            ])->where('pid', '=', $this->id)->select([
                'id',
                'pid',
                'like',
                'is_author',
                'content',
                'user_id',
                'created_at',
            ]);
    }
    
    public function parent()
    {
        return $this->belongsTo(
            self::class,
            'pid',
            'id'
        );
    }
    
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }
    
    public function exp()
    {
        return $this->belongsTo(
            UserExp::class,
            'exp_id',
            'id'
        );
    }
}
