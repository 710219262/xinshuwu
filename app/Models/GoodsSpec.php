<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 08/03/2019
 * Time: 00:33
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GoodsSpec
 *
 * @package App\Models
 * @property integer $id
 * @property integer $name
 * @property integer $status
 */
class GoodsSpec extends Model
{
    protected $table = 'xsw_goods_spec';
    protected $guarded = ['id'];
    
    public function value()
    {
        return $this->hasOne(GoodsSpecValue::class, 'id', 'spec_id');
    }
}
