<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 07/04/2019
 * Time: 17:06
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTag extends Model
{
    protected $table = 'xsw_user_tag';
    
    protected $guarded = ['id'];
}
