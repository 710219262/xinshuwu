<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 23/12/2018
 * Time: 15:22
 */

namespace App\Services;

use OSS\OssClient;

class OssService
{
    /**
     * @param $key
     * @return string
     */
    public static function getUploadsKey($key)
    {
        if (strstr($key, '/')) {
            return $key;
        }

        return sprintf("uploads/$key");
    }
    
    /**
     * 上传文件
     * @param       $key
     * @param       $content
     * @param array $options
     * @throws \OSS\Core\OssException
     */
    public static function upload($key, $content, $options = [])
    {
        $key = self::getUploadsKey($key);
        if (!self::isExist($key)) {
            /** @var OssClient $oss */
            $oss = app('aliyun-oss');
            $oss->uploadFile(config('aliyun.oss.bucket'), $key, $content, $options);
        }
    }
    
    /**
     * 该file_md5 key是否已经上传过了
     * @param $key
     * @return bool
     */
    public static function isExist($key)
    {
        $key = self::getUploadsKey($key);
        /** @var OssClient $oss */
        $oss = app('aliyun-oss');
        return $oss->doesObjectExist(config('aliyun.oss.bucket'), $key);
    }
    
    /**
     * 返回Raw SQL拼接CDN域名
     * @param string $key
     * @return string
     */
    public static function getRawSql($key = 'url')
    {
        return \DB::raw(
            sprintf(
                "IF({$key} !='',CONCAT('%s',%s),{$key}) as {$key}",
                config('aliyun.oss.upload_domain'),
                $key
            )
        );
    }
    
    /**
     * 上传文件
     * @param $key
     * @return array
     */
    public static function info($key)
    {
        $key = self::getUploadsKey($key);
        /** @var OssClient $oss */
        $oss = app('aliyun-oss');
        return $oss->getObjectMeta(config('aliyun.oss.bucket'), $key);
    }
    
    /**
     * 压缩图片 默认到1.5MB
     * 因为OCR要求base64到1.5mb以内
     * @param       $key string oss key
     * @param float $size less than 1.5mb
     * @return string 压缩后的全路径
     */
    public static function compressBySize($key, $size = 1.5 * (1 << 20))
    {
        $meta = static::info($key);
        $originalSize = $meta['content-length'];
        if ($size > $originalSize) {
            return self::getFullUrl($key);
        }
        
        $percent = floatval($size * 1.0 / $originalSize) * 100;
        
        return static::compressByPercent($key, $percent);
    }
    
    /**
     * @param $key
     * @param $percent
     * @return string
     */
    public static function compressByPercent($key, $percent)
    {
        return sprintf("%s?x-oss-process=image/quality,q_%d", self::getFullUrl($key), $percent);
    }
    
    /**
     * 拼接全路径
     * @param $key
     * @return string
     */
    public static function getFullUrl($key)
    {
        return sprintf("%s%s", config('aliyun.oss.upload_domain'), $key);
    }
    
    /**
     * 获取oss object base64编码
     * @param      $urlOrKey
     * @param bool $isKey
     * @return string
     */
    public static function encodeB64($urlOrKey, $isKey = false)
    {
        if ($isKey) {
            $urlOrKey = self::getFullUrl($urlOrKey);
        }
        
        return base64_encode(file_get_contents($urlOrKey));
    }
}
