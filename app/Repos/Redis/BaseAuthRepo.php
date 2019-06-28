<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 07/03/2019
 * Time: 15:27
 */

namespace App\Repos\Redis;

use App\Cache\CacheKey;
use Illuminate\Support\Facades\Redis;

class BaseAuthRepo
{
    protected $redis;
    
    public function __construct()
    {
        $this->redis = Redis::connection();
    }
    
    /**
     * @param $phone
     *
     * @return string
     */
    public function getVerifyCode($phone)
    {
        return $this->redis->get(CacheKey::getVerifyCodeKey($phone));
    }
    
    /**
     * @param $phone
     */
    public function delVerifyCode($phone)
    {
        $this->redis->del([CacheKey::getVerifyCodeKey($phone)]);
    }
}
