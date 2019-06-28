<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/02/2019
 * Time: 14:18
 */

namespace App\Http\Controllers\MMS;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ExpressCompany;
use App\Models\GoodsCategory;
use App\Models\MerchantAccount;
use App\Repos\Common\RegionRepo;
use App\Repos\Redis\AdminAuthRepo;
use App\Repos\Redis\MerchantAuthRepo;
use App\Services\AuthService;
use App\Services\OssService;
use App\Services\SmsService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class Common extends Controller
{
    const CHINA_REGION = 86;
    
    /**
     * 获取地区列表
     *
     * @param Request    $request
     * @param RegionRepo $repo
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getRegionList(Request $request, RegionRepo $repo)
    {
        $this->validate($request, [
            'pid'        => 'integer|exists:xsw_region,id',
            'is_foreign' => 'boolean',
        ]);
        
        
        $where['parent_id'] = $request->input('pid', 0);
        $where['is_foreign'] = $request->input('is_foreign', false);
        
        if (!$where['is_foreign'] && $where['parent_id'] === 0) {
            $where['parent_id'] = self::CHINA_REGION;
        }
        
        $regions = $repo->getRegionList($where, [
            'region_id',
            'name',
            'name_en',
            'is_foreign',
            'level',
        ]);
        
        return json_response($regions);
    }
    
    /**
     * 上传接口
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function upload(Request $request)
    {
//        $this->validate($request, [
//            'file' => 'required',
//        ]);
        
        $file = $request->file('file');
        if ($file->getSize() > 0) {
            $mime = $file->getMimeType();
            $key = md5_file($file);
            
            
            try {
                OssService::upload($key, $file, ['Content-Type' => $mime]);
                $domain = config('aliyun.oss.upload_domain');
                return json_response([
                    'key' => $key,
                    'url' => "{$domain}{$key}",
                ]);
            } catch (\Exception $e) {
                return json_response([], '上传失败', 409);
            }
        } else {
            return json_response([], '上传失败，请重试', 409);
        }
    }
    
    /**
     * 获取验证码
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function getSmsCode(Request $request)
    {
        $this->validate(
            $request,
            [
                'phone' => [
                    'required',
                    'regex:/^1(3|4|5|6|7|8|9)[0-9]{9}$/',
                ],
            ],
            [
                'phone.required' => '手机号不能为空',
                'phone.regex'    => '手机号不符合规范',
            ]
        );
        
        $code = generateVerifyCode();
        
        return SmsService::sendVerifyCode(
            $request->input('phone'),
            $code
        );
    }
    
    /**
     * 登录获取验证码
     *
     * @param Request     $request
     * @param AuthService $authService
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function auth(Request $request, AuthService $authService)
    {
        $this->validate($request, [
            'phone' => 'required',
            'code'  => 'required|size:4',
            'role'  => 'required|in:admin,merchant',
        ], [
            'phone.required' => '手机号不能为空',
            'code.required'  => '验证码不能为空',
            'code.size'      => '验证码格式有误',
        ]);
        
        $role = $request->input('role');
        
        if (MerchantAccount::ROLE === $role) {
            return $authService->mmsMerchantAuth(
                $request->input('phone'),
                $request->input('code')
            );
        }
        
        return $authService->mmsAdminAuth(
            $request->input('phone'),
            $request->input('code')
        );
    }
    
    /**
     * 登录密码登录
     *
     * @param Request     $request
     * @param AuthService $authService
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authpass(Request $request, AuthService $authService)
    {
        $this->validate($request, [
            'phone'    => 'required',
            'password' => 'required',
            'role'     => 'required|in:admin,merchant',
        ], [
            'phone.required'    => '手机号不能为空',
            'password.required' => '密码不能为空',
        ]);
        
        $role = $request->input('role');
        
        if (MerchantAccount::ROLE === $role) {
            return $authService->mmsMerchantAuthPass(
                $request->input('phone'),
                $request->input('password')
            );
        }

        return $authService->mmsAdminAuthPass(
            $request->input('phone'),
            $request->input('password')
        );
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getUserInfo(Request $request)
    {
        /** @var MerchantAccount|Admin $user */
        $user = $request->user();
        
        return json_response([
            'role'       => $user::ROLE,
            'name'       => $user->name,
            'status'     => intval($user->status),
            'account_id' => $user->id,
            'logo'       => $user instanceof MerchantAccount ? $user->logo : '',
        ]);
    }
    
    /**
     * @param Request          $request
     * @param MerchantAuthRepo $merchantAuthRepo
     * @param AdminAuthRepo    $adminAuthRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function logout(
        Request $request,
        MerchantAuthRepo $merchantAuthRepo,
        AdminAuthRepo $adminAuthRepo
    ) {
        $token = $request->header('X-Token');
        $role = $request->header('X-Role');
        
        if (MerchantAccount::ROLE === $role) {
            $merchantAuthRepo->revokeToken($token);
        }
        
        if (Admin::ROLE === $role) {
            $adminAuthRepo->revokeToken($token);
        }
        
        return json_response();
    }
    
    /**
     * 获取商品分类
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getGoodsCategory()
    {
        return json_response(GoodsCategory::query()
            ->where('level', '=', 0)
            ->select(['id', 'name'])
            ->get()
        );
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getExpressCompany(Request $request)
    {
        $this->validate($request, [
            'keywords' => 'string',
        ]);
        
        $builder = ExpressCompany::query()
            ->where('status', ExpressCompany::ENABLE);
        
        if ($request->has('keywords')) {
            $keywords = trim($request->input('keywords'));
            $builder->where(function ($q) use ($keywords) {
                /** @var Builder $q */
                $q->where('name', 'LIKE', "%$keywords%")
                    ->orWhere('abbr', 'LIKE', "%$keywords%");
            });
        }
        
        return json_response($builder->get([
            'id',
            'name',
            'abbr',
        ]));
    }
    
    /**
     * 返回OSS的签名验证
     *
     * @return JSON 签名信息
     */
    public function getGetkey(Request $request)
    {
        $id = 'LTAITDdDzBsNkgxk';
        $key = 'iYGhALym1KReYvVZnXkzcwvLGYvPq9';
        $host = 'https://' . env('OSS_BUCKET') . '.' . env('OSS_ENDPOINT');
        $callbackUrl = '';
        $dir = 'uploads/video/';
        
        //$callback_param = array('callbackUrl'=>$callbackUrl,
        //    'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
        //    'callbackBodyType'=>"application/x-www-form-urlencoded");
        //$callback_string = json_encode($callback_param);
        
        //$base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 30;  //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问。
        $end = $now + $expire;
        $expiration = $this->gmt_iso8601($end);
        
        
        //最大文件大小.用户可以自己设置
        $condition = array(0 => 'content-length-range', 1 => 0, 2 => 1048576000);
        $conditions[] = $condition;
        
        // 表示用户上传的数据，必须是以$dir开始，不然上传会失败，这一步不是必须项，只是为了安全起见，防止用户通过policy上传到别人的目录。
        $start = array(0 => 'starts-with', 1 => '$key', 2 => $dir);
        $conditions[] = $start;
        
        
        $arr = array('expiration' => $expiration, 'conditions' => $conditions);
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));
        
        $response = array();
        $response['accessid'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        //$response['callback'] = $base64_callback_body;
        $response['dir'] = $dir;  // 这个参数是设置用户上传文件时指定的前缀。
        echo json_encode($response);
    }
    
    //格式化时间,格式为2016-07-07T23:48:43Z
    function gmt_iso8601($time)
    {
        $dtStr = date("c", $time);
        $pos = strpos($dtStr, '+');
        $expiration = substr($dtStr, 0, $pos);
        return $expiration . "Z";
    }
    
    //测试用的回调函数
    public function postCallback(Request $request)
    {
        // 1.获取OSS的签名header和公钥url header
        $authorizationBase64 = "";
        $pubKeyUrlBase64 = "";
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authorizationBase64 = $_SERVER['HTTP_AUTHORIZATION'];
        }
        if (isset($_SERVER['HTTP_X_OSS_PUB_KEY_URL'])) {
            $pubKeyUrlBase64 = $_SERVER['HTTP_X_OSS_PUB_KEY_URL'];
        }
        if ($authorizationBase64 == '' || $pubKeyUrlBase64 == '') {
            header("http/1.1 403 Forbidden");
            exit();
        }
        
        // 2.获取OSS的签名
        $authorization = base64_decode($authorizationBase64);  //OSS签名
        
        // 3.获取公钥
        $pubKeyUrl = base64_decode($pubKeyUrlBase64);  //公钥的URL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pubKeyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $pubKey = curl_exec($ch);
        if ($pubKey == "") {
            exit();   //header("http/1.1 403 Forbidden");
        }
        
        // 4.获取回调body,上传的图片的相关信息都在这里
        $body = file_get_contents('php://input');
        
        // 5.拼接待签名字符串
        $authStr = '';
        $path = $_SERVER['REQUEST_URI'];
        $pos = strpos($path, '?');
        if ($pos === false) {
            $authStr = urldecode($path) . "\n" . $body;
        } else {
            $authStr = urldecode(substr($path, 0, $pos)) . substr($path, $pos, strlen($path) - $pos) . "\n" . $body;
        }
        
        // 6.验证签名
        $ok = openssl_verify($authStr, $authorization, $pubKey, OPENSSL_ALGO_MD5);
        if ($ok == 1) {
            header("Content-Type: application/json");
            $data = array("Status" => "Ok");
            return response()->json($data);
        } else {
            exit(); //header("http/1.1 403 Forbidden");
        }
    }
}
