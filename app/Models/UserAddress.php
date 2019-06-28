<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/03/2019
 * Time: 23:00
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserAddress
 *
 * @property integer $id
 * @property integer $user_id
 * @property string  $contact
 * @property string  $phone
 * @property string  $address
 * @property string  $number
 * @property string  $tag
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 *
 * @package App\Models
 */
class UserAddress extends Model
{
    use SoftDeletes;
    
    protected $table = 'xsw_user_address';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'is_default' => 'boolean',
        'pid'        => 'integer',
        'cid'        => 'integer',
        'aid'        => 'integer',
    ];
    
    const T_HOME = 'HOME';
    const T_COMPANY = 'COMPANY';
    const T_OTHER = 'OTHER';
    
    /**
     * @param $pId
     * @param $cId
     * @param $aId
     *
     * @return string
     */
    public static function getRegionText($pId, $cId, $aId)
    {
        $regions = Region::query()
            ->whereIn('region_id', [$pId, $cId, $aId])
            ->get();
        
        $text = [];
        
        foreach ($regions as $region) {
            /** @var Region $region */
            $text [] = $region->name;
        }
        
        return implode($text, " ");
    }
}
