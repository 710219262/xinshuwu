<?php


namespace App\Events\User;

use App\Events\Contract\Notification as Contract;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserFollow;
use Carbon\Carbon;

class UserWasFollowed implements Contract
{
    public $userFollow;
    public $time;
    
    /**
     * UserWasFollowed constructor.
     *
     * @param UserFollow $userFollow
     */
    public function __construct(UserFollow $userFollow)
    {
        $this->userFollow = $userFollow;
        $this->time = Carbon::now();
    }
    
    /**
     * @inheritDoc
     */
    public function buildPayload()
    {
        /** @var User $user */
        $user = User::query()->find($this->userFollow->follower_id);
        
        return [
            'user' => [
                'nickname' => $user->nickname,
                'avatar'   => $user->avatar,
                'id'       => $user->id,
            ],
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function getActionUserId()
    {
        return $this->userFollow->follower_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getReceiverUserId()
    {
        return $this->userFollow->followed_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getAction()
    {
        return Notification::A_FOLLOW;
    }
    
    /**
     * @inheritDoc
     */
    public function getJump()
    {
        return Notification::J_USER;
    }
    
    /**
     * @inheritDoc
     */
    public function getTarget()
    {
        return Notification::T_USER;
    }
    
    /**
     * @inheritDoc
     */
    public function getReferId()
    {
        return $this->userFollow->follower_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->time;
    }
    
}
