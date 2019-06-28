<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 20/04/2019
 * Time: 23:38
 */

namespace App\Repos\Redis;

use App\Cache\CacheKey;
use Illuminate\Support\Facades\Redis;

class UserCacheRepo
{
    protected $redis;
    
    public function __construct()
    {
        $this->redis = Redis::connection();
    }
    
    /**
     * @param $id
     * @param $logisticNo
     *
     * @return string
     */
    public function getLogisticQueryTs($id, $logisticNo)
    {
        return  $this->redis->get(CacheKey::getUserLogisticQueryKey($id, $logisticNo));
    }
    
    /**
     * @param $id
     * @param $orderNo
     */
    public function setLogisticQueryTs($id, $orderNo)
    {
        $this->redis->set(CacheKey::getUserLogisticQueryKey($id, $orderNo), time());
    }
}
