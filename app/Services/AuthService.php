<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 06/03/2019
 * Time: 15:50
 */

namespace App\Services;

use App\Models\Admin;
use App\Models\MerchantAccount;
use App\Repos\Redis\AdminAuthRepo;
use App\Repos\Redis\MerchantAuthRepo;
use App\Repos\Redis\UserAuthRepo;

class AuthService
{
    /**
     * MMS 每小时最大登录失败次数
     */
    const MMS_MAX_FAILED_PER_HOUR = 10;
    /**
     * 用户 每小时最大登录失败次数
     */
    const USER_MAX_FAILED_PER_HOUR = 10;
    
    protected $merchantAuthRepo;
    protected $adminAuthRepo;
    
    /**
     * AuthService constructor.
     *
     * @param MerchantAuthRepo $merchantAuthRepo
     * @param AdminAuthRepo    $adminAuthRepo
     */
    public function __construct(
        MerchantAuthRepo $merchantAuthRepo,
        AdminAuthRepo $adminAuthRepo
    ) {
        $this->merchantAuthRepo = $merchantAuthRepo;
        $this->adminAuthRepo = $adminAuthRepo;
    }
    
    /**
     * MMS商户登录/注册
     *
     * @param $phone
     * @param $code
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function mmsMerchantAuth($phone, $code)
    {
        if ($this->merchantAuthRepo->getAuthFrq($phone) > self::MMS_MAX_FAILED_PER_HOUR) {
            return json_response([], '你尝试次数过多，请稍后再试', 429);
        }
        
        if ($this->merchantAuthRepo->getVerifyCode($phone) !== $code) {
            $this->merchantAuthRepo->incAuthFrq($phone);
            return json_response([], '验证码错误', 401);
        }
        
        $this->merchantAuthRepo->delVerifyCode($phone);
        
        /** @var MerchantAccount $merchant */
        $merchant = MerchantAccount::query()
            ->firstOrCreate(['phone' => $phone], [
                'phone' => $phone,
                'name'  => sprintf('猩事物%s', $phone),
                'password'  => md5('00000'),
            ]);
        
        $token = generateMMSToken();
        $this->merchantAuthRepo->setToken($token, $phone);
        
        return json_response([
            'token'  => $token,
            'role'   => MerchantAccount::ROLE,
            'name'   => $merchant->name,
            'status' => intval($merchant->status),
        ]);
    }

    /**
     * MMS商户登录/注册
     *
     * @param $phone
     * @param $password
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function mmsMerchantAuthPass($phone, $password)
    {
        /** @var MerchantAccount $merchant */
        $merchant = MerchantAccount::query()
            ->where('phone', $phone)
            ->where('password', md5($password))
            ->first();
        if(!empty($merchant)) {
            $token = generateMMSToken();
            $this->merchantAuthRepo->setToken($token, $phone);

            return json_response([
                'token' => $token,
                'role' => MerchantAccount::ROLE,
                'name' => $merchant->name,
                'status' => intval($merchant->status),
            ]);
        }else{
            return json_response([], '用户名或者密码错误', 401);
        }
    }

    /**
     * @param $phone
     * @param $code
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function mmsAdminAuth($phone, $code)
    {
        if ($this->adminAuthRepo->getAuthFrq($phone) > self::MMS_MAX_FAILED_PER_HOUR) {
            return json_response([], '你尝试次数过多，请稍后再试', 429);
        }
        /** @var Admin $admin */
        $admin = Admin::query()
            ->where(['phone' => $phone])
            ->first();
        
        if (empty($admin)) {
            return json_response([], '账号或验证码错误', 401);
        }
        
        if ($this->adminAuthRepo->getVerifyCode($phone) !== $code) {
            $this->adminAuthRepo->incAuthFrq($phone);
            return json_response([], '验证码错误', 401);
        }
        
        $this->adminAuthRepo->delVerifyCode($phone);
        
        
        $token = generateMMSToken();
        $this->adminAuthRepo->setToken($token, $phone);
        
        return json_response([
            'token'  => $token,
            'role'   => Admin::ROLE,
            'name'   => $admin->name,
            'status' => intval($admin->status),
        ]);
    }

    /**
     *
     * @param $phone
     * @param $password
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function mmsAdminAuthPass($phone, $password)
    {
        /** @var MerchantAccount $merchant */
        $admin = Admin::query()
            ->where('phone', $phone)
            ->where('password', md5($password))
            ->first();

        if(!empty($admin)) {
            $token = generateMMSToken();
            $this->adminAuthRepo->setToken($token, $phone);

            return json_response([
                'token'  => $token,
                'role'   => Admin::ROLE,
                'name'   => $admin->name,
                'status' => intval($admin->status),
            ]);
        }else{
            return json_response([], '管理员用户名或者密码错误', 401);
        }
    }
}
