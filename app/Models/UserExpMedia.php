<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 14/04/2019
 * Time: 15:43
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserExpMedia extends Model
{
    protected $table = 'xsw_user_exp_media';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'width'  => 'integer',
        'height' => 'integer',
    ];
    
    const T_VIDEO = 'VIDEO';
    const T_IMAGE = 'IMG';
}
