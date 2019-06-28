<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 14/04/2019
 * Time: 01:09
 */

namespace App\Services;

use Requests;

class LogisticInfoService
{
    /**
     * @param        $logisticNo
     * @param string $type
     *
     * @return \Requests_Response
     */
    public static function request($logisticNo, $type = '')
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'charset'       => 'UTF-8',
            'Authorization' => sprintf("APPCODE %s", config('logistic.app_code')),
        ];
        
        $query = http_build_query([
            'no'   => $logisticNo,
            'type' => $type,
        ]);
        
        $response = Requests::get(config('logistic.api_host') . "?$query", $headers);
        
        if ($response->success) {
            $info = json_decode($response->body, true);
        } else {
            $info = [];
        }
        
        return $info;
    }
}
