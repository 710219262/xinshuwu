<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 07/03/2019
 * Time: 15:22
 */

namespace App\Repos\Redis;

use App\Cache\CacheKey;

class AdminAuthRepo extends MMSAuthBaseRepo
{
    /**
     * @param $token
     * @return string
     */
    public function getToken($token)
    {
        return $this->redis->hget(CacheKey::MMS_ADMIN_TOKEN, $token);
    }
    
    
    /**
     * @param $token
     * @param $phone
     */
    public function setToken($token, $phone)
    {
        $this->redis->hset(CacheKey::MMS_ADMIN_TOKEN, $token, $phone);
        
        $this->redis->zadd(CacheKey::MMS_ADMIN_TOKEN_TS, [
            $token => time(),
        ]);
    }
    
    /**
     * @param $token
     */
    public function revokeToken($token)
    {
        $this->redis->hdel(CacheKey::MMS_ADMIN_TOKEN, $token);
    }
}
