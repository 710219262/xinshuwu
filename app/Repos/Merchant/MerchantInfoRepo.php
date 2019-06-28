<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 07/03/2019
 * Time: 16:40
 */

namespace App\Repos\Merchant;

use App\Models\MerchantAccount;
use App\Models\MerchantInfo;
use App\Models\MerchantTransaction;

class MerchantInfoRepo
{
    /**
     * @param $status
     *
     * @return array
     */
    public function getList($status)
    {
        $status = is_array($status) ? $status : [$status];
        
        return MerchantInfo::query()
            ->with(['merchantAccount'])
            ->whereIn('status', $status)
            ->get()
            ->toArray();
    }
    
    public function getMerchantInfo($id)
    {
        /** @var MerchantInfo $merchant */
        $merchantInfo = MerchantInfo::query()
            ->with(['productImg:merchant_id,url', 'certsImg:merchant_id,url'])
            ->find($id);
        
        return $merchantInfo;
    }

    public function getMerchantAccount($id)
    {
        /** @var MerchantInfo $merchant */
        $merchantAccount = MerchantAccount::query()
            ->find($id);

        return $merchantAccount;
    }
    
    /**
     * @param integer $id
     * @param string  $result REJECT | PASS
     * @param string  $reason
     */
    public function checkOrReject($id, $result, $reason = '')
    {
        /** @var MerchantInfo $merchantInfo */
        $merchantInfo = MerchantInfo::query()->find($id);
        
        if (MerchantInfo::R_PASS === $result) {
            $merchantInfo->update([
                'status' => MerchantAccount::S_CHECKED,
            ]);
            
            $merchantInfo->merchantAccount->update([
                'status'      => MerchantAccount::S_CHECKED,
                'category_id' => $merchantInfo->product_category_id,
            ]);
        }
        
        if (MerchantInfo::R_REJECT === $result) {
            $merchantInfo->update([
                'status'        => MerchantAccount::S_REJECTED,
                'reject_reason' => $reason,
            ]);
            
            $merchantInfo->merchantAccount->update([
                'status' => MerchantAccount::S_REJECTED,
            ]);
        }
    }
}
