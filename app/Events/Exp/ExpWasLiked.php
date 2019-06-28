<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 15/04/2019
 * Time: 22:29
 */

namespace App\Events\Exp;

use App\Events\Contract\Notification as NotificationContract;
use App\Events\Event;
use App\Models\Notification;
use App\Models\UserExpLike;
use Carbon\Carbon;

class ExpWasLiked extends Event implements NotificationContract
{
    protected $userExpLike;
    protected $time;
    
    public function __construct(UserExpLike $userExpLike)
    {
        $this->userExpLike = $userExpLike;
        $this->time = Carbon::now();
    }
    
    /**
     * @inheritDoc
     */
    public function buildPayload()
    {
        $user = $this->userExpLike->user;
        $exp = $this->userExpLike->exp;
        
        return [
            'user' => [
                'nickname' => $user->nickname,
                'avatar'   => $user->avatar,
                'id'       => $user->id,
            ],
            'exp'  => [
                'title' => $exp->title,
                'media' => $exp->mediaRlt()->first(['url', 'type', 'height', 'width']) ?? "",
            ],
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function getActionUserId()
    {
        return $this->userExpLike->user->id;
    }
    
    /**
     * @inheritDoc
     */
    public function getReceiverUserId()
    {
        return $this->userExpLike->exp->user_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getAction()
    {
        return Notification::A_LIKE;
    }
    
    /**
     * @inheritDoc
     */
    public function getTarget()
    {
        return Notification::T_EXP;
    }
    
    /**
     * @inheritDoc
     */
    public function getJump()
    {
        return Notification::J_EXP;
    }
    
    /**
     * @inheritDoc
     */
    public function getReferId()
    {
        return $this->userExpLike->exp->id;
    }
    
    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->time;
    }
}
