<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 08/03/2019
 * Time: 12:18
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GoodsImg
 *
 * @package App\Models
 *
 * @property integer $id
 * @property integer $goods_id
 * @property string  $url
 * @property string  $type
 */
class GoodsImg extends Model
{
    const T_BANNER = 'BANNER';
    const T_INFO = 'INFO';
    
    protected $table = 'xsw_goods_img';
    protected $guarded = ['id'];
}
