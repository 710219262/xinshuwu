<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 22/02/2019
 * Time: 13:50
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductCategory
 * @package App\Models
 * @property integer $id
 * @property string  $name
 * @property Carbon  $deleted_at
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class GoodsCategory extends Model
{
    protected $table = 'xsw_goods_category';
    
    protected $guarded = ['id'];
}
