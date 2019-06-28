<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 08/03/2019
 * Time: 12:07
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GoodsSpecValue
 *
 * @package App\Models
 * @property integer   $id
 * @property integer   $spec_id
 * @property string    $value
 * @property GoodsSpec $spec
 * @property string    $full_value
 */
class GoodsSpecValue extends Model
{
    protected $table = 'xsw_goods_spec_value';
    protected $guarded = ['id'];
    
    public function getFullValueAttribute()
    {
        return sprintf("%s:%s", $this->spec->name, $this->value);
    }
    
    public function spec()
    {
        return $this->belongsTo(GoodsSpec::class, 'spec_id', 'id');
    }
}
