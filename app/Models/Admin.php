<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 07/03/2019
 * Time: 15:48
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Admin
 * @package App\Models
 * @property integer $id
 * @property string  $phone
 * @property string  $name
 * @property integer $status
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class Admin extends Model
{
    const ROLE = 'admin';
    
    protected $table = 'xsw_admin';
    
    protected $guarded = ['id'];
}
