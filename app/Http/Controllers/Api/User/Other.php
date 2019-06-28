<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 27/04/2019
 * Time: 20:30
 */

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserShare;
use App\Repos\Share\ShareRepo;
use Illuminate\Http\Request;

class Other extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function info(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|string|exists:xsw_user',
        ]);
        
        /** @var User $user */
        $user = User::query()->find($request->input('id'));
        
        $isFollowed = false;
        
        $request->user() && $isFollowed = $user->followerRlt()->where('follower_id', $request->user()->id)->exists();
        
        $user = $user->only([
            'id',
            'nickname',
            'motto',
            'gender',
            'avatar',
            'fans_count',
            'follow_count',
            'liked_count',
            'favorite_count',
            'is_vip',
            'vip_card',
        ]);
        
        $user['share_count'] = UserShare::query()
            ->where('user_id', $user['id'])
            ->count();
        
        $user['vip_card'] = $user['vip_card'] ?: "";
        $user['is_followed'] = $isFollowed;
        
        return json_response($user);
    }
    
    /**
     * personal share list(user exp list)
     *
     * @param Request   $request
     * @param ShareRepo $shareRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request, ShareRepo $shareRepo)
    {
        $this->validate($request, [
            'id' => 'required|string|exists:xsw_user',
        ]);
        
        /** @var User $user */
        $user = User::query()->find($request->input('id'));
        
        return json_response($shareRepo->list_to_other($user));
    }
    
    /**
     * @param Request   $request
     * @param ShareRepo $shareRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function linkList(Request $request, ShareRepo $shareRepo)
    {
        $this->validate($request, [
            'id' => 'required|string|exists:xsw_user',
        ]);
        
        /** @var User $user */
        $user = User::query()->find($request->input('id'));
        
        return json_response($shareRepo->linkList($user));
    }
}
