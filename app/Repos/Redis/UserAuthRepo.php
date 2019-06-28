<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 18/03/2019
 * Time: 22:32
 */

namespace App\Repos\Redis;

use App\Cache\CacheKey;

class UserAuthRepo extends BaseAuthRepo
{
    /**
     * @param $token
     *
     * @return string
     */
    public function getToken($token)
    {
        return $this->redis->hget(CacheKey::USER_TOKEN, $token);
    }
    
    /**
     * @param $phone
     *
     * @return string
     */
    public function getAuthFrq($phone)
    {
        $frq = $this->redis->get(CacheKey::getUserAuthFrqKey($phone));
        return intval($frq);
    }
    
    /**
     * @param $phone
     */
    public function incAuthFrq($phone)
    {
        $this->redis->incr(CacheKey::getUserAuthFrqKey($phone));
    }
    
    /**
     * @param $userId
     *
     * @return string
     */
    public function issueToken($userId)
    {
        $token = generateUserToken();
        
        $this->redis->hset(CacheKey::USER_TOKEN, $token, $userId);
        
        $this->redis->zadd(CacheKey::USER_TOKEN_TS, [
            $token => time(),
        ]);
        
        return $token;
    }
    
    /**
     * @param $token
     */
    public function revokeToken($token)
    {
        $this->redis->hdel(CacheKey::USER_TOKEN, $token);
    }
}
