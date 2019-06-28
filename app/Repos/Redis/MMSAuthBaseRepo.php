<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 18/03/2019
 * Time: 22:31
 */

namespace App\Repos\Redis;

use App\Cache\CacheKey;

class MMSAuthBaseRepo extends BaseAuthRepo
{
    /**
     * @param $phone
     * @return string
     */
    public function getAuthFrq($phone)
    {
        $frq = $this->redis->get(CacheKey::getMMSAuthFrqKey($phone));
        return intval($frq);
    }
    
    /**
     * @param $phone
     */
    public function incAuthFrq($phone)
    {
        $this->redis->incr(CacheKey::getMMSAuthFrqKey($phone));
    }
}
