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
use App\Models\UserFollow;
use App\Models\UserShare;
use App\Models\UserTag;
use App\Repos\Redis\UserAuthRepo;
use App\Repos\User\UserRepo;
use App\Services\AuthService;
use App\Services\SmsService;
use Illuminate\Http\Request;

class User extends Controller
{
    /**
     * 登录或注册
     *
     * @param Request      $request
     *
     * @param UserRepo     $userRepo
     *
     * @param UserAuthRepo $userAuthRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function auth(Request $request, UserRepo $userRepo, UserAuthRepo $userAuthRepo)
    {
        $this->validate($request, [
            'by'         => 'required|string|in:qq,wechat,weibo,phone',
            'wechat_uid' => 'required_if:by,wechat',
            'wb_uid'     => 'required_if:by,weibo',
            'qq_uid'     => 'required_if:by,qq',
            'phone'      => ['required_if:by,phone', 'regex:/^1(3|4|5|6|7|8|9)[0-9]{9}$/'],
            'code'       => 'required_if:by,phone',
        ], [
            'phone.regex' => '手机号不符合规范',
        ]);
        
        $by = $request->input('by');
        
        if (UserModel::L_PHONE === $by) {
            $phone = $request->input('phone');
            $code = $userAuthRepo->getVerifyCode($phone);
            
            if ($userAuthRepo->getAuthFrq($phone) > AuthService::USER_MAX_FAILED_PER_HOUR) {
                return json_response([], '你尝试次数过多，请稍后再试', 429);
            }
            
            if ($code !== $request->input('code')) {
                $userAuthRepo->incAuthFrq($phone);
                return json_response([], '验证码输入错误', 401);
            }
        }
        if (UserModel::L_PHONE === $by) {
            /** @var UserModel $user */
            $user = $userRepo->getUserOrNew(
                $by,
                $request->input(UserModel::L_ID_MAP[$by]),
                [
                    'phone' => $request->input('phone', ''),
                ]
            );
        }

        if (UserModel::L_WECHAT === $by) {
            $user = $userRepo->getUserById(
                $by,
                $request->input('wechat_uid', '')
            );
            if(empty($user)){
                return json_response([], '此用户未注册或未绑定手机号', 401);
            }
        }

        $token = $userAuthRepo->issueToken($user->id);
        
        return json_response([
            'token' => $token,
            'user'  => [
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
    
    /**
     * @param Request      $request
     *
     * @param UserRepo     $userRepo
     * @param UserAuthRepo $userAuthRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request, UserRepo $userRepo, UserAuthRepo $userAuthRepo)
    {
        $this->validate($request, [
            'by'         => 'required|string|in:qq,wechat,weibo,phone',
            'wechat_uid' => 'required_if:by,wechat|unique:xsw_user',
            'wb_uid'     => 'required_if:by,weibo|unique:xsw_user',
            'qq_uid'     => 'required_if:by,qq|unique:xsw_user',
            'phone'      => 'required_if:by,phone|unique:xsw_user',
            'code'       => 'required_if:by,phone|string',
            'nickname'   => 'required|string',
            'gender'     => 'required|string|in:MALE,FEMALE',
            'avatar'     => 'string',
            'origin'     => 'string|in:andriod,ios,web,wap,wechat,other'
        ]);
        
        $by = $request->input('by');
        
        if (UserModel::L_PHONE === $by) {
            $phone = $request->input('phone');
            $code = $userAuthRepo->getVerifyCode($phone);
            
            if ($userAuthRepo->getAuthFrq($phone) > AuthService::USER_MAX_FAILED_PER_HOUR) {
                return json_response([], '你尝试次数过多，请稍后再试', 429);
            }
            
            if ($code !== $request->input('code')) {
                $userAuthRepo->incAuthFrq($phone);
                return json_response([], '验证码输入错误', 401);
            }
        }
        
        
        /** @var UserModel $user */
        $user = $userRepo->getUserOrNew(
            $by,
            $request->input(UserModel::L_ID_MAP[$by]),
            [
                'gender'   => $request->input('gender', 'MALE'),
                'nickname' => $request->input('nickname', ''),
                'avatar'   => $request->input('avatar', ''),
                'phone'    => $request->input('phone', ''),
                'origin'    => $request->input('origin', ''),
            ]
        );
        
        $token = $userAuthRepo->issueToken($user->id);
        
        return json_response([
            'token' => $token,
        ]);
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateInfo(Request $request)
    {
        $this->validate($request, [
            'avatar'   => 'string',
            'birthday' => 'string|date',
            'nickname' => 'string',
            'motto'    => 'string',
            'gender'   => 'string|in:MALE,FEMALE',
        ]);
        
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        $user->update(array_filter($request->only([
            'avatar',
            'birthday',
            'nickname',
            'motto',
            'gender',
        ])));
        
        return json_response($user);
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getFollowedList(Request $request)
    {
        /** @var UserModel $user */
        $user = $request->user();
        
        $followed = $user->followed()->get();
        $followed->map(function ($item) {
            $item->followed_id = $item->follower_id;
            unset($item->follower_id);
        });
        return json_response($followed);
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getFollowerList(Request $request)
    {
        /** @var UserModel $user */
        $user = $request->user();
        
        $follower = $user->follower();
        
        return json_response($follower);
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function followUser(Request $request)
    {
        $this->validate($request, [
            'followed_id' => 'required|int|exists:xsw_user,id',
        ]);
        
        /** @var UserModel $user */
        $user = $request->user();
        
        $followedId = $request->input('followed_id');
        
        if ($user->id !== $followedId && 0 === UserFollow::query()
                ->where('follower_id', $user->id)
                ->where('followed_id', $followedId)->count()) {
            /** @var UserFollow $uf */
            $uf = UserFollow::query()->create([
                'follower_id' => $user->id,
                'followed_id' => $followedId,
            ]);
            event(new UserWasFollowed(($uf)));
        }
        
        return json_response($user->followed()->get());
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function unfollowUser(Request $request)
    {
        $this->validate($request, [
            'followed_id' => 'required|int|exists:xsw_user,id',
        ]);
        
        /** @var UserModel $user */
        $user = $request->user();
        
        $user->userFollowRlt()->where(
            'followed_id',
            $request->input('followed_id')
        )->delete();
        
        return json_response();
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function getCode(Request $request)
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
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function updateTag(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'tags'   => 'required|array',
            'tags.*' => 'required|int|exists:xsw_goods_category,id',
        ]);
        
        try {
            \DB::beginTransaction();
            $user->tagRlt()->delete();
            $tags = $request->input('tags');
            
            foreach ($tags as $categoryId) {
                UserTag::query()->create([
                    'user_id'     => $user->id,
                    'category_id' => $categoryId,
                ]);
            }
            \DB::commit();
            return json_response();
        } catch (\Exception $e) {
            \DB::rollBack();
            return json_response([], '保存失败');
        }
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function info(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        $user = $user->only([
            'id',
            'nickname',
            'birthday',
            'motto',
            'gender',
            'avatar',
            'fans_count',
            'follow_count',
            'liked_count',
            'favorite_count',
            'phone',
            'is_vip',
            'vip_card',
        ]);
        
        $user['share_count'] = UserShare::query()
            ->where('user_id', $user['id'])
            ->count();
        
        $user['vip_card'] = $user['vip_card'] ?: "";
        
        return json_response($user);
    }
}
