<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 25/02/2019
 * Time: 16:00
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MerchantImg
 * @package App\Models
 * @property int    $id
 * @property int    $merchant_id
 * @property string $type
 * @property string $url
 * @property Carbon $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MerchantImg extends Model
{
    const IMG_ID_CARD = 'ID_CARD';
    
    const IMG_CERT = 'CERT';
    
    const IMG_PRODUCT = 'PRODUCT';
    
    protected $table = 'xsw_merchant_img';
    
    protected $guarded = ['id'];
}
