<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/02/2019
 * Time: 18:50
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MerchantRepo
 *
 * @property int             $id
 * @property string          $trade_mode
 * @property int             $ship_region_id
 * @property array           $ship_region_ids
 * @property int             $company_region_id
 * @property array           $company_region_ids
 * @property string          $company_name
 * @property string          $license_url
 * @property string          $credit_code
 * @property string          $license_register_addr
 * @property Carbon          $license_validate_before
 * @property string          $id_card_front
 * @property string          $id_card_back
 * @property string          $product_brand
 * @property int             $product_category_id
 * @property string          $product_link
 * @property string          $contact
 * @property string          $phone
 * @property string          $wechat
 * @property string          $emg_contact
 * @property string          $emg_phone
 * @property int             $consignee_region_id
 * @property array           $consignee_region_ids
 * @property string          $consignee_addr
 * @property integer         $status
 * @property string          $reject_reason
 * @property Carbon          $created_at
 * @property Carbon          $updated_at
 * @property Carbon          $deleted_at
 * @property MerchantAccount $merchantAccount
 * @package App\Models
 */
class MerchantInfo extends Model
{
    use SoftDeletes;
    //trade_mode 商家
    const MERCHANT = 'MERCHANT';
    //trade_mode 个人买手
    const SINGLE = 'SINGLE';
    
    const R_REJECT = 'REJECT';
    const R_PASS = 'PASS';
    
    protected $table = 'xsw_merchant_info';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'ship_region_ids'      => 'array',
        'company_region_ids'   => 'array',
        'consignee_region_ids' => 'array',
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function merchantAccount()
    {
        return $this->belongsTo(MerchantAccount::class, 'account_id', 'id');
    }
    
    public function productImg()
    {
        return $this->hasMany(MerchantImg::class, 'merchant_id', 'id')
            ->where('type', MerchantImg::IMG_PRODUCT);
    }
    
    public function certsImg()
    {
        return $this->hasMany(MerchantImg::class, 'merchant_id', 'id')
            ->where('type', MerchantImg::IMG_CERT);
    }
}
