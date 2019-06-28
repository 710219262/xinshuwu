<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 06/03/2019
 * Time: 17:20
 */

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\MerchantAccount;
use App\Repos\Redis\AdminAuthRepo;
use App\Repos\Redis\MerchantAuthRepo;
use Closure;

class Auth
{
    protected $merchantAuthRepo;
    protected $adminAuthRepo;
    
    
    public function __construct(
        MerchantAuthRepo $merchantAuthRepo,
        AdminAuthRepo $adminAuthRepo
    ) {
        $this->merchantAuthRepo = $merchantAuthRepo;
        $this->adminAuthRepo = $adminAuthRepo;
    }
    
    /**
     * @param   \Illuminate\Http\Request $request
     * @param Closure                    $next
     * @param string                     $needRole
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|mixed
     */
    public function handle($request, Closure $next, $needRole = MerchantAccount::ROLE)
    {
        $unauthorizedRsp = json_response([], '请先登录', 401);
        
        $token = $request->header('X-Token');
        $role = $request->header('X-Role');
        
        if (empty($token) || empty($role)) {
            return $unauthorizedRsp;
        }
        
        $tokenExpired = json_response([], '登录超时', 400);
        $permissionDenied = json_response([], '你无权访问当前页面', 403);
        
        if (Admin::ROLE !== $role && $needRole !== $role) {
            return $permissionDenied;
        }
        
        if (MerchantAccount::ROLE === $role) {
            $phone = $this->merchantAuthRepo->getToken($token);
            if (empty($phone) || MerchantAccount::query()->where('phone', $phone)->count() === 0) {
                return $tokenExpired;
            }
            
            $request->setUserResolver(function () use ($phone) {
                return MerchantAccount::query()->where('phone', $phone)
                    ->first();
            });
        }
        
        if (Admin::ROLE === $role) {
            $phone = $this->adminAuthRepo->getToken($token);
            if (empty($phone) || Admin::query()->where('phone', $phone)->count() === 0) {
                return $tokenExpired;
            }
            
            $request->setUserResolver(function () use ($phone) {
                return Admin::query()->where('phone', $phone)
                    ->first();
            });
        }
        
        
        return $next($request);
    }
}
