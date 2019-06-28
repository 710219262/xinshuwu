<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 27/04/2019
 * Time: 18:27
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XSearch extends Model
{
    protected $table = 'xsw_x_search';
    protected $guarded = ['id'];
    
    const T_ARTICLE = 'ARTICLE';
    const T_COMMUNICATION = 'COMMUNICATION';
    const T_NOVEL = 'NOVEL';
    const T_FRIEND = 'FRIEND';
    const T_SNACK = 'SNACK';
}
