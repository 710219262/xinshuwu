<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 15/04/2019
 * Time: 23:18
 */

namespace App\Events\Exp;

use App\Events\Contract\Notification as NotificationContract;
use App\Events\Event;
use App\Models\Notification;
use App\Models\UserCollection;
use App\Models\UserExp;
use Carbon\Carbon;

class ExpWasCollected extends Event implements NotificationContract
{
    protected $collection;
    /**
     * @var UserExp
     */
    protected $exp;
    
    protected $time;
    
    public function __construct(UserCollection $collection)
    {
        $this->collection = $collection;
        $this->exp = UserExp::query()->find($collection->collect_id);
        $this->time = Carbon::now();
    }
    
    /**
     * @inheritDoc
     */
    public function buildPayload()
    {
        $user = $this->collection->user;
        
        return [
            'user' => [
                'nickname' => $user->nickname,
                'avatar'    => $user->avatar,
                'id'        => $user->id,
            ],
            'exp'  => [
                'title' => $this->exp->title,
                'media' => $this->exp->mediaRlt()->first(['url', 'type', 'height', 'width']) ?? "",
            ],
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function getActionUserId()
    {
        return $this->collection->user_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getReceiverUserId()
    {
        return $this->exp->user_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getAction()
    {
        return Notification::A_COLLECT;
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
    public function getTarget()
    {
        return Notification::T_EXP;
    }
    
    /**
     * @inheritDoc
     */
    public function getReferId()
    {
        return $this->collection->collect_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->time;
    }
}
