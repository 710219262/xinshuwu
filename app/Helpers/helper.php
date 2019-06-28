<?php

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @param array $data
 * @param       $message
 * @param int   $status
 * @param array $headers
 *
 * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
 */
function json_response($data = [], $message = 'ok', $status = 200, array $headers = [])
{
    if ($data instanceof Model || $data instanceof Collection) {
        $data = $data->toArray();
    }
    
    $response = [
        'code'    => $status,
        'message' => array_get($data, 'message', $message),
        'data'    => $data ? $data : [],
    ];
    
    if ($status != 200) {
        unset($response['data']);
    }
    
    return response($response, $status, $headers);
}

/**
 * 生成随机4位纯数字短信验证码
 *
 * @return string
 */
function generateVerifyCode()
{
    return strval(rand(1000, 9999));
}

/**
 * 生成token
 *
 * @return string
 */
function generateMMSToken()
{
    return str_random(32);
}

/**
 * 生成token
 *
 * @return string
 */
function generateUserToken()
{
    return str_random(32);
}

/**
 * @param $yuan
 *
 * @return int
 */
function yuan_to_fen($yuan)
{
    return intval($yuan * 100);
}

/**
 * @param $left
 * @param $right
 *
 * @return string
 */
function my_add($left, $right)
{
    return bcadd($left, $right, 2);
}

/**
 * @param $left
 * @param $right
 *
 * @return string
 */
function my_mul($left, $right)
{
    return bcmul($left, $right, 2);
}

/**
 * @param $left
 * @param $right
 *
 * @return string
 */
function my_sub($left, $right)
{
    return bcsub($left, $right, 2);
}

/**
 * @param $left
 * @param $right
 *
 * @return string
 */
function my_div($left, $right)
{
    return bcdiv($left, $right, 2);
}

/**
 * @param $data
 * @param $paging
 * @return array
 */
function paging($data, $paging)
{
    $page = intval(array_get($paging, 'page', 1)) ?: 1;
    $limit = intval(array_get($paging, 'limit', 99999)) ?: 99999;

    $total = collect($data)->count();
    $quotient = intval($total / $limit);
    $remainder = $total % $limit;

    $totalPage = $quotient + $remainder ? 1 : 0;

    $res = [
        'data'       => collect($data)->forPage($page, $limit)->values(),
        'total'      => $total,
        'total_page' => $totalPage,
        'cur_page'   => $page,
        'limit'      => $limit,
    ];

    return $res;
}
