<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/02/2019
 * Time: 14:06
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Region
 * @package App\Models
 * @property integer $id
 * @property integer $region_id
 * @property integer $parent_id
 * @property string  $name
 * @property string  $region_name
 * @property string  $name_en
 * @property boolean $is_foreign
 * @property integer $level
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class Region extends Model
{
    protected $table = 'xsw_region';
    
    protected $guarded = ['id'];
    
    protected $casts = ['is_foreign' => 'boolean'];
}
