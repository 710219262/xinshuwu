<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/03/2019
 * Time: 18:55
 */

namespace App\Http\Middleware;

use App\Models\User;
use App\Repos\Redis\UserAuthRepo;
use Closure;

class Token
{
    protected $userAuthRepo;
    
    public function __construct(UserAuthRepo $userAuthRepo)
    {
        $this->userAuthRepo = $userAuthRepo;
    }
    
    /**
     * @param   \Illuminate\Http\Request $request
     * @param Closure                    $next
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('X-TOKEN');
        $userId = $this->userAuthRepo->getToken($token);
        
        $user = User::query()->find($userId);
        
        if (empty($user)) {
            return json_response([], '请先登录再进行操作', 401);
        }
        
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        return $next($request);
    }
}
