<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 18/03/2019
 * Time: 21:57
 */

namespace App\Http\Controllers\Api\User;

use App\Events\User\UserWasFollowed;
use App\Http\Controllers\Controller;
use App\Models\User as UserModel;
use App\Models\User;
use App\Models\UserFollow;
use App\Models\UserShare;
use App\Models\UserTag;
use App\Repos\Redis\UserAuthRepo;
use App\Repos\User\UserRepo;
use App\Services\AuthService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Ixudra\Curl\Facades\Curl;

class Wechat extends Controller
{

    public function __construct()
    {
    }

    public function getOpenId(Request $request)
    {
        $code = $request->input('code');

        if ($code) {
            $params = http_build_query([
                'appid' => 'wx283608153c3dd962',
                'secret' => '61816990a70379fb210d6af5fe73a820',
                'code' => $code,
                'grant_type' => 'authorization_code'
            ]);
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?' . $params;
            $result = file_get_contents($url);
            $result = json_decode($result, true);
            $res = array('status'=>1,'data'=>array(),'msg'=>'OK');
            $res['data']['accessToken'] = $result['access_token'];
            $res['data']['refreshToken'] = $result['refresh_token'];
            $res['data']['userInfo'] = $this->getWxUserInfo($result['access_token'],$result['refresh_token']);
            echo json_encode($res);
        }
    }

    public function getUserInfo(Request $request)
    {
        $code = $request->input('code');
        if ($code) {
            $params = http_build_query([
                'appid' => 'wx283608153c3dd962',
                'secret' => '61816990a70379fb210d6af5fe73a820',
                'code' => $code,
                'grant_type' => 'authorization_code'
            ]);
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?' . $params;
            $result = file_get_contents($url);
            $result = json_decode($result, true);
            if(empty($result['access_token'])){
                return json_response($result, '个人信息获取错误，请稍后重试', 401);
            }
            $userinfo= $this->getWxUserInfo($result['access_token'],$result['refresh_token']);
            return json_response($userinfo);
        }else{
            return json_response([], 'code输入错误', 401);
        }
    }

    public function bindPhone(Request $request, UserRepo $userRepo, UserAuthRepo $userAuthRepo)
    {
        $this->validate($request, [
            'by'         => 'required|string|in:qq,wechat,weibo,phone',
            'phone'      => 'required_if:by,phone',
            'code'       => 'required|string',
        ]);
//        $this->validate($request, [
//            'by'         => 'required|string|in:qq,wechat,weibo,phone',
//            'wechat_uid' => 'required_if:by,wechat',
//            'wb_uid'     => 'required_if:by,weibo',
//            'qq_uid'     => 'required_if:by,qq',
//            'phone'      => 'required_if:by,phone',
//            'code'       => 'required|string',
//            'nickname'   => 'required|string',
//            'gender'     => 'required|string|in:MALE,FEMALE',
//            'avatar'     => 'string',
//        ]);
        $by = $request->input('by');

        $phone = $request->input('phone');
        $code = $userAuthRepo->getVerifyCode($phone);

        if ($userAuthRepo->getAuthFrq($phone) > AuthService::USER_MAX_FAILED_PER_HOUR) {
            return json_response([], '你尝试次数过多，请稍后再试', 401);
        }

        if ($code !== $request->input('code')) {
            $userAuthRepo->incAuthFrq($phone);
            return json_response([], '验证码输入错误', 401);
        }

        $uid = $userRepo->bindUserStatus($by,$request->input(UserModel::L_ID_MAP[$by]),['phone'    => $request->input('phone', '')]);
        if($uid){
            $user_temp = User::query()->where('id', $uid)->first();
            $token = $userAuthRepo->issueToken($uid);
            return json_response([
                'token' => $token,
                'user'  => [
                    'wechat_uid'       => $user_temp->wechat_uid,
                    'gender'      => $user_temp->gender,
                    'nickname'    => $user_temp->nickname,
                    'avatar'      => $user_temp->avatar,
                    'phone'       => $user_temp->phone,
                    'birthday'    => $user_temp->birthday,
                    'motto'       => $user_temp->motto,
                    'is_new_user' => $user_temp->tagRlt()->count() === 0,
                ],
            ]);
            //return json_response([], '该手机号已经绑定用户', 401);
        }

        /** @var UserModel $user */
        $user = $userRepo->bindUserOrNew(
            $by,
            $request->input(UserModel::L_ID_MAP[$by]),
            [
                'gender'   => $request->input('gender', 'MALE'),
                'nickname' => $request->input('nickname', ''),
                'avatar'   => $request->input('avatar', ''),
                'phone'    => $request->input('phone', '')
            ]
        );

        $token = $userAuthRepo->issueToken($user->id);

        return json_response([
            'token' => $token,
            'user'  => [
                'wechat_uid'       => $user->wechat_uid,
                'gender'      => $user->gender,
                'nickname'    => $user->nickname,
                'avatar'      => $user->avatar,
                'phone'       => $user->phone,
                'birthday'    => $user->birthday,
                'motto'       => $user->motto,
                'is_new_user' => $user->tagRlt()->count() === 0,
            ],
        ]);
    }


    public function getUserInfo222(Request $request)
    {
        $access_token = $request->input('access_token');
        $openid = $request->input('openid');
        return  $this->getWxUserInfo($access_token,$openid);
    }

    public function getWxUserInfo($access_token, $openid)
    {
            $params = http_build_query([
                'access_token' => $access_token,
                'openid' => $openid,
                'lang' => 'zh_CN'
            ]);
            $url = 'https://api.weixin.qq.com/sns/userinfo?' . $params;
            $result = file_get_contents($url);
            $result = json_decode($result, true);
            return  $result;
    }

    public function getShare(Request $request)
    {
        $this->validate($request, [
            'url'       => 'required|string'
        ]);
        $url = $request->input('url');
        $appid = "wx283608153c3dd962";
        $secret = "61816990a70379fb210d6af5fe73a820";
        //缓存内是否存在accessToken。
        $accessToken = Cache::remember('accessToken118', 120, function () use ($appid, $secret) {
            //获取access_token的请求地址
//            $accessTokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
//            //请求地址获取access_token
//            $accessTokenJson = file_get_contents($accessTokenUrl);
//            $accessTokenObj = json_decode($accessTokenJson);
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
            $res = $this->curl($url, 'get', 'json');
            $res = json_decode($res, true);
            $accessToken = $res['access_token'];//$accessTokenObj->access_token;
            return $accessToken;
        });
        //获取jsapi_ticket的请求地址
//        $ticketUrl = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$accessToken&type=jsapi";
//        $jsapiTicketJson = file_get_contents($ticketUrl);
//        $jsapiTicketObj = json_decode($jsapiTicketJson);
//        $jsapiTicket = $jsapiTicketObj->ticket;

        $jsapiTicket = Cache::remember('jsapiTicket999', 120, function () use ($accessToken) {
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $accessToken .'&type=jsapi';
            $res = $this->curl($url, 'get', 'json');
            $res = json_decode($res, true);
            $jsapiTicket = $res['ticket'];//$accessTokenObj->access_token;
            return $jsapiTicket;
        });
//        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $accessToken .'&type=jsapi';
//        $res = $this->curl($url, 'get', 'json');
//        $res = json_decode($res, true);
//        $jsapiTicket = $res['ticket'];//$accessTokenObj->access_token;

        $noncestr = str_random(16);
        $time = time();
        //拼接string1
        $jsapiTicketNew = 'jsapi_ticket=' . $jsapiTicket . '&noncestr=' . $noncestr . '&timestamp=' . $time . '&url=' . $url;
        //对string1作sha1加密
        $signature = sha1($jsapiTicketNew);
        $data = [
            'appId' => $appid,
            'timeStamp' => $time,
            'nonceStr' => $noncestr,
            'signature' => $signature,
            'jsapiTicket' => $jsapiTicket,
            'url' => $url,
            'jsApiList' => [
                'onMenuShareAppMessage', 'onMenuShareTimeline'
            ]
        ];
        return json_response($data);
    }

    /**
     * 请求接口方法
     */
    public function curl($url, $type = 'get', $data = '')
    {

        //Curl处理
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        if ($type = 'post') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public function getConfig(Request $request)
    {
        $this->validate($request, [
            'url'       => 'required|string'
        ]);
        $url = $request->input('url');
        $appid = "wx283608153c3dd962";
        $secret = "61816990a70379fb210d6af5fe73a820";
        //缓存内是否存在accessToken。
        $accessToken = Cache::remember('accessToken118', 120, function () use ($appid, $secret) {
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
            $res = $this->curl($url, 'get', 'json');
            $res = json_decode($res, true);
            $accessToken = $res['access_token'];//$accessTokenObj->access_token;
            return $accessToken;
        });

        $jsapiTicket = Cache::remember('jsapiTicket999', 120, function () use ($accessToken) {
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $accessToken .'&type=jsapi';
            $res = $this->curl($url, 'get', 'json');
            $res = json_decode($res, true);
            $jsapiTicket = $res['ticket'];//$accessTokenObj->access_token;
            return $jsapiTicket;
        });

        $noncestr = str_random(16);
        $time = time();
        //拼接string1
        $jsapiTicketNew = 'jsapi_ticket=' . $jsapiTicket . '&noncestr=' . $noncestr . '&timestamp=' . $time . '&url=' . $url;
        //对string1作sha1加密
        $signature = sha1($jsapiTicketNew);
        $data = [
            'appId' => $appid,
            'timeStamp' => $time,
            'nonceStr' => $noncestr,
            'signature' => $signature,
            'jsapiTicket' => $jsapiTicket,
            'url' => $url,
            'jsApiList' => [
            ]
        ];
        return json_response($data);
    }
}


