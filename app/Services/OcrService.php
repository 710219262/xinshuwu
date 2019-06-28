<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/02/2019
 * Time: 15:08
 */

namespace App\Services;

use App\Cache\CacheKey;
use Carbon\Carbon;
use Requests;
use Cache;

class OcrService
{
    /**
     * 识别营业执照
     *
     * @param $img string oss image url
     *
     * @return array
     * Example:
     * {
     *   "config_str" : "null\n", #配置字符串信息
     *   "angle" : float, #输入图片的角度（顺时针旋转），［0， 90， 180，270］
     *   "reg_num" : string, #注册号，没有识别出来时返回"FailInRecognition"
     *   "name" : string, #公司名称，没有识别出来时返回"FailInRecognition"
     *   "type" : string, #公司类型，没有识别出来时返回"FailInRecognition"
     *   "person" : string, #公司法人，没有识别出来时返回"FailInRecognition"
     *   "establish_date": string, #公司注册日期(例：证件上为"2014年04月16日"，算法返回"20140416")
     *   "valid_period": string, #公司营业期限终止日期(例：证件上为"2014年04月16日至2034年04月15日"，算法返回"20340415")
     *   #当前算法将日期格式统一为输出为"年月日"(如"20391130"),并将"长期"表示为"29991231"，若证件上没有营业期限，则默认其为"长期",返回"29991231"。
     *   "address" : string, #公司地址，没有识别出来时返回"FailInRecognition"
     *   "capital" : string, #注册资本，没有识别出来时返回"FailInRecognition"
     *   "business": string, #经营范围，没有识别出来时返回"FailInRecognition"
     *   "emblem" : string, #国徽位置［top,left,height,width］，没有识别出来时返回"FailInDetection"
     *   "title" : string, #标题位置［top,left,height,width］，没有识别出来时返回"FailInDetection"
     *   "stamp" : string, #印章位置［top,left,height,width］，没有识别出来时返回"FailInDetection"
     *   "qrcode" : string, #二维码位置［top,left,height,width］，没有识别出来时返回"FailInDetection"
     *   "success" : bool, #识别成功与否 true/false
     *   "request_id": string
     * }
     * @throws \Exception
     */
    public static function request($img)
    {
        try {
            $key = CacheKey::getKey(CacheKey::OCR_RESPONSE, $img);
            if (Cache::has($key)) {
                $response = Cache::get($key);
            } else {
                $compressedImg = OssService::compressBySize($img);
                $b64EncodedImg = OssService::encodeB64($compressedImg);
                
                $headers = [
                    'Content-Type'  => 'application/json',
                    'charset'       => 'UTF-8',
                    'Authorization' => sprintf("APPCODE %s", config('ocr.app_code')),
                ];
                
                $data = json_encode([
                    'image' => $b64EncodedImg,
                ]);
                $response = Requests::post(config('ocr.api_host'), $headers, $data);
            }
            /**
             * 464 means invalid business license
             * Requests_Response {#108
             * +body: "Invalid Result - invalid business license"
             * +raw: """
             * HTTP/1.1 464 464\r\n
             */
            if (!$response->success || $response->status_code === 200 || $response->status_code === 464) {
                Cache::forever($key, $response);
                $response = json_decode($response->body, true);
                if (!array_get($response, 'success', false)) {
                    throw new \Exception("阿里云识别营业执照失败, 请尝试上传正确且清晰的图片");
                }
                return self::parseOcrResponse($response);
            }
        } catch (\Exception $e) {
            throw new \Exception("阿里云识别营业执照接口异常: " . $e->getMessage());
        }
        
        throw new \Exception("阿里云识别营业执照接口异常");
    }
    
    /**
     * 解析OCR识别营业执照的body
     *
     * @param $response
     *
     * @return array
     */
    public static function parseOcrResponse($response)
    {
        return [
            'credit_code'             => self::checkFail(array_get($response, 'reg_num')),
            'company_name'            => self::checkFail(array_get($response, 'name')),
            'license_register_addr'   => self::checkFail(array_get($response, 'address')),
            'license_validate_before' => self::checkTime(self::checkFail(array_get($response, 'valid_period'))),
        ];
    }
    
    /**
     * check if current key recognition failed
     *
     * @param $string
     *
     * @return string
     */
    protected static function checkFail($string)
    {
        if ($string === 'FailInRecognition') {
            return '';
        }
        
        return $string;
    }
    
    /**
     * parse 20181011 to 2018-10-11
     *
     * @param $time
     *
     * @return string
     */
    protected static function checkTime($time)
    {
        if ($time == "") {
            return $time;
        }
        
        return Carbon::createFromFormat('Ymd', $time)->toDateString();
    }
}
