<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 06/03/2019
 * Time: 17:22
 */

namespace App\Repos\Redis;

use App\Cache\CacheKey;

/**
 * Class MerchantAuthRepo
 * @package App\Repos\Redis
 */
class MerchantAuthRepo extends MMSAuthBaseRepo
{
    /**
     * @param $token
     * @return string
     */
    public function getToken($token)
    {
        return $this->redis->hget(CacheKey::MMS_MERCHANT_TOKEN, $token);
    }
    
    
    /**
     * @param $token
     * @param $phone
     */
    public function setToken($token, $phone)
    {
        $this->redis->hset(CacheKey::MMS_MERCHANT_TOKEN, $token, $phone);
        
        $this->redis->zadd(CacheKey::MMS_MERCHANT_TOKEN_TS, [
            $token => time(),
        ]);
    }
    
    /**
     * @param $token
     */
    public function revokeToken($token)
    {
        $this->redis->hdel(CacheKey::MMS_MERCHANT_TOKEN, $token);
    }
}
