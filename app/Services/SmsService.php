<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 06/03/2019
 * Time: 13:21
 */

namespace App\Services;

use AlibabaCloud\Client\AlibabaCloud;
use App\Cache\CacheKey;
use Cache;

class SmsService
{
    const VERIFY_CODE_TTL_IN_MIN = 5;
    const MAX_SEND_PER_HOUR = 10;
    
    /**
     * 发送验证码
     *
     * @see https://github.com/aliyun/openapi-sdk-php-client/blob/master/README-CN.md
     *
     * @param $data
     *
     * @throws \Exception
     */
    protected static function send($data)
    {
        AlibabaCloud::accessKeyClient(
            config('aliyun.access_key'),
            config('aliyun.access_secret')
        )->regionId('cn-hangzhou')->asGlobalClient();
        
        try {
            $result = AlibabaCloud::rpcRequest()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'PhoneNumbers'  => array_get($data, 'phone'),
                        'SignName'      => env('SMS_SIGN_NAME'),
                        'TemplateCode'  => array_get($data, 'tlp_code'),
                        'TemplateParam' => array_get($data, 'tlp_param'),
                    ],
                ])
                ->request();
            
            \Log::info("sms send", [$data, $result]);
        } catch (\Exception $e) {
            throw new \Exception(sprintf("短信发送失败:%s", $e->getMessage()));
        }
    }
    
    /**
     * 发送验证码
     *
     * @param $phone
     * @param $code
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public static function sendVerifyCode($phone, $code)
    {
        $msg = '发送成功，请注意查收';
        
        $inWhiteList = false;
        if (in_array($phone, config('xsw.whitelist'))) {
            $code = '1234';
            $inWhiteList = true;
        }
        
        $frqCacheKey = CacheKey::getVerifyFrqKey($phone);
        $verifyCodeKey = CacheKey::getVerifyCodeKey($phone);
        
        if (Cache::get($frqCacheKey, 0) > static::MAX_SEND_PER_HOUR) {
            return json_response([], '你发送频率过于频繁，歇息一下吧 🙂', 429);
        }
        
        if (app()->environment() !== 'local'  && !$inWhiteList) {
            self::send([
                'phone'     => $phone,
                'tlp_code'  => env('SMS_TPL_VERIFY'),
                'tlp_param' => json_encode(['code' => $code]),
            ]);
        }
        
        Cache::put($verifyCodeKey, $code, static::VERIFY_CODE_TTL_IN_MIN);
        Cache::increment($frqCacheKey);
        
        if (app()->environment() == 'local') {
            return json_response(['code' => $code], $msg);
        }
        
        return json_response([], $msg);
    }
    
    /**
     * 发送审核店铺成功短信
     *
     * @param $phone
     * @param $code
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public static function sendCheckMerchant($phone)
    {
        $msg = '店铺审核结果信息发送成功';
        
        if (app()->environment() !== 'local') {
            self::send([
                'phone'    => $phone,
                'tlp_code' => env('SMS_TPL_CheckMerchant'),
            ]);
        }
        
        return json_response([], $msg);
    }

    /**
     * 发送店铺新订单通知短信
     *
     * @param $phone
     * @param $code
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public static function sendMerchantOrder($phone)
    {
        $msg = '店铺新订单通知信息发送成功';

        if (app()->environment() !== 'local') {
            self::send([
                'phone'    => $phone,
                'tlp_code' => env('SMS_TPL_NotifyMerchantOrder'),
            ]);
        }

        return json_response([], $msg);
    }

    /**
     * 发送赠送会员通知短信
     *
     * @param $phone
     * @param $code
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public static function sendVip($phone)
    {
        $msg = '赠送会员通知信息发送成功';

        if (app()->environment() !== 'local') {
            self::send([
                'phone'    => $phone,
                'tlp_code' => env('SMS_TPL_NotifySendVIP'),
            ]);
        }

        return json_response([], $msg);
    }
}
