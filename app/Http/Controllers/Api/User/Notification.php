<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17/04/2019
 * Time: 23:21
 */

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Notification as NotificationModel;
use App\Models\UserFollow;
use App\Repos\User\NotificationRepo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Notification extends Controller
{
    /**
     * @param Request          $request
     *
     * @param NotificationRepo $notificationRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request, NotificationRepo $notificationRepo)
    {
        $this->validate($request, [
            'type' => 'required|string|in:LIKE_CLT,FLW,CMT',
        ]);
        
        $type = $request->input('type');
        
        $notifications = $notificationRepo->list(
            $request->user(),
            $type
        );
        
        $notifications->transform(function ($n) use ($type) {
            /** @var NotificationModel $n */
            if (NotificationModel::A_FOLLOW === $type) {
                $n['is_followed'] = UserFollow::query()
                    ->where('follower_id', $n->r_user_id)
                    ->where('followed_id', $n->a_user_id)
                    ->exists();
            }
            Carbon::setLocale('Zh');
            $created_at = $n->created_at->diffForHumans();
            $n = $n->toArray();
            $n['created_at'] = $created_at;
            return $n;
        });
        
        return json_response($notifications);
    }
    
    /**
     * @param Request          $request
     *
     * @param NotificationRepo $notificationRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function read(Request $request, NotificationRepo $notificationRepo)
    {
        $this->validate($request, [
            'id' => 'required|integer|exists:xsw_user_notification',
        ], [
            'id.exists' => '消息不见咯~',
        ]);
        
        $notificationRepo->read($request->input('id'));
        
        return json_response();
    }
}
