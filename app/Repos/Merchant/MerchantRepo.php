<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 22/02/2019
 * Time: 14:00
 */

namespace App\Repos\Merchant;

use App\Models\MerchantAccount;
use App\Models\MerchantInfo;
use App\Models\MerchantImg;

class MerchantRepo
{
    /**
     * 商户入驻
     *
     * @param $data
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function createMerchant($data)
    {
        $data = array_only($data, [
            'account_id',
            'trade_mode',
            'ship_region_id',
            'ship_addr',
            'ship_region_ids',
            'company_name',
            'company_region_id',
            'company_region_ids',
            'license_url',
            'credit_code',
            'license_register_addr',
            'license_validate_before',
            'product_brand',
            'product_category_id',
            'product_link',
            'contact',
            'phone',
            'wechat',
            'emg_contact',
            'emg_phone',
            'consignee_region_id',
            'consignee_region_ids',
            'consignee_addr',
        ]);
        
        $data = array_filter($data);
        $data['status'] = MerchantAccount::S_CHECKING;
        
        return MerchantInfo::query()
            ->create($data);
    }
    
    /**
     * 个人买手入驻
     *
     * @param $data
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function createSingle($data)
    {
        $data = array_only($data, [
            'account_id',
            'trade_mode',
            'ship_region_id',
            'ship_addr',
            'ship_region_ids',
            'company_region_id',
            'company_region_ids',
            'company_region',
            'id_card_front',
            'id_card_back',
            'product_brand',
            'product_category_id',
            'product_link',
            'contact',
            'phone',
            'wechat',
            'emg_contact',
            'emg_phone',
            'consignee_region_id',
            'consignee_region_ids',
            'consignee_addr',
        ]);
        
        $data = array_filter($data);
        
        $data['company_region_ids'] = array_get($data, 'company_region_ids', []);
        $data['status'] = MerchantAccount::S_CHECKING;

        return MerchantInfo::query()
            ->create($data);
    }
    
    /**
     * 创建商户图片
     *
     * @param       $merchantId
     * @param       $type
     * @param array $imgs
     */
    public function createMerchantImg($merchantId, $type, array $imgs)
    {
        foreach ($imgs as $url) {
            MerchantImg::query()
                ->create([
                    'merchant_id' => $merchantId,
                    'type'        => $type,
                    'url'         => $url,
                ]);
        }
    }
}
