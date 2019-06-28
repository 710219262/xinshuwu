<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/03/2019
 * Time: 20:16
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserFollow
 *
 * @package App\Models
 * @property integer $id
 * @property integer $follower_id
 * @property integer $followed_id
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property Carbon  $deleted_at
 */
class UserFollow extends Model
{
    use SoftDeletes;
    protected $table = 'xsw_user_follow';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'follower_id' => 'integer',
        'followed_id' => 'integer',
    ];
}
