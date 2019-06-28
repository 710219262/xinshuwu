<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17/04/2019
 * Time: 22:52
 */

namespace App\Http\Middleware;

use App\Models\User;
use App\Repos\Redis\UserAuthRepo;
use Closure;

class Tourist
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
            $request->setUserResolver(function () {
                return null;
            });
        } else {
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
        }
        
        return $next($request);
    }
}
