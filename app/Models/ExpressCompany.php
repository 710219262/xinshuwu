<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 20/04/2019
 * Time: 20:43
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExpressCompany
 *
 * @package App\Models
 * @property integer $id
 * @property string  $name
 * @property string  $abbr
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class ExpressCompany extends Model
{
    protected $table = 'xsw_express_company';
    
    protected $guarded = ['id'];
    
    const ENABLE = 1;
    const DISABLE = 0;
}
