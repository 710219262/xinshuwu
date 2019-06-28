<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 04/04/2019
 * Time: 19:33
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Feedback
 *
 * @package App\Models
 * @property string $id
 * @property string $user_id
 * @property string $content
 * @property string $mobile
 * @property string $created_at
 * @property string $updated_at
 */
class Feedback extends Model
{
    protected $table = 'xsw_feedback';
    
    protected $guarded = ['id'];
}
