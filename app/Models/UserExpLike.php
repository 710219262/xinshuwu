<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 15/04/2019
 * Time: 22:52
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserExpLike
 *
 * @package App\Models
 * @property integer $id
 * @property integer $exp_id
 * @property integer $user_id
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property User    $user
 * @property UserExp $exp
 */
class UserExpLike extends Model
{
    protected $table = 'xsw_user_exp_like';
    
    protected $guarded = ['id'];
    
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
