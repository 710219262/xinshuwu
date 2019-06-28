<?php

namespace App\Cache;

use Carbon\Carbon;

class CacheKey
{
    const OCR_RESPONSE = "ocr:%s";
    // 验证码
    const VERIFY_CODE = "sms:verify:%s";
    // 验证码发送频率 hour:mobile
    const VERIFY_CODE_FRQ = "sms:verify:frq:%s:%s";
    
    // MMS登录失败频率
    const MMS_AUTH_FRQ = "mms:auth:failed:%s:%s";
    
    // MMS merchant token
    const MMS_MERCHANT_TOKEN = "mms:merchant:token";
    // MMS merchant token timestamp
    const MMS_MERCHANT_TOKEN_TS = "mms:merchant:token:ts";
    
    // MMS token
    const MMS_ADMIN_TOKEN = "mms:admin:token";
    // MMS token timestamp
    const MMS_ADMIN_TOKEN_TS = "mms:merchant:token:ts";
    
    // 用户登录失败频率
    const USER_AUTH_FRQ = 'user:auth:failed:%s:%s';
    // user token
    const USER_TOKEN = "user:token";
    // user token timestamp
    const USER_TOKEN_TS = "user:token:ts";
    
    // user:order_no query logistic timestamp
    const USER_LOGISTIC_TS = "user:%s:logistic_no:%s";
    
    /**
     * @param $key
     * @param $args
     *
     * @return string
     */
    public static function getKey($key, $args)
    {
        $args = is_array($args) ? $args : [$args];
        return vsprintf($key, $args);
    }
    
    public static function getVerifyCodeKey($phone)
    {
        return self::getKey(self::VERIFY_CODE, $phone);
    }
    
    public static function getVerifyFrqKey($phone)
    {
        $hour = Carbon::now()->format('YmdH');
        return self::getKey(self::VERIFY_CODE_FRQ, [$hour, $phone]);
    }
    
    public static function getMMSAuthFrqKey($phone)
    {
        $hour = Carbon::now()->format('YmdH');
        return self::getKey(self::MMS_AUTH_FRQ, [$hour, $phone]);
    }
    
    public static function getUserAuthFrqKey($phone)
    {
        $hour = Carbon::now()->format('YmdH');
        return self::getKey(self::USER_AUTH_FRQ, [$hour, $phone]);
    }
    
    public static function getUserLogisticQueryKey($id, $no)
    {
        return self::getKey(self::USER_LOGISTIC_TS, [$id, $no]);
    }
}
