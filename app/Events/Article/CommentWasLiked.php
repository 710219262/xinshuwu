<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 21:03
 */

namespace App\Events\Article;

use App\Events\Contract\Notification as Contract;
use App\Models\ArticleCmtLike;
use App\Models\Notification;
use Carbon\Carbon;

class CommentWasLiked implements Contract
{
    protected $ArticleCmtLIke;
    protected $time;
    
    /**
     * CommentWasLiked constructor.
     *
     * @param ArticleCmtLIke $articleCmtLike
     */
    public function __construct(ArticleCmtLike $articleCmtLike)
    {
        $this->ArticleCmtLIke = $articleCmtLike;
        $this->time = Carbon::now();
    }
    
    /**
     * @inheritdoc
     */
    public function buildPayload()
    {
        
        $user = $this->ArticleCmtLIke->user;
        $comment = $this->ArticleCmtLIke->comment;
        $article = $comment->article;
        
        return [
            'user'    => [
                'nickname' => $user->nickname,
                'avatar'   => $user->avatar,
                'id'       => $user->id,
            ],
            'article' => [
                'title'   => $article->title,
                'comment' => $comment->content,
                'cover'   => $article->cover,
            ],
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function getActionUserId()
    {
        return $this->ArticleCmtLIke->user->id;
    }
    
    /**
     * @inheritDoc
     */
    public function getReceiverUserId()
    {
        return $this->ArticleCmtLIke->comment->user_id;
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
        return Notification::T_ARTICLE;
    }
    
    /**
     * @inheritDoc
     */
    public function getJump()
    {
        return Notification::J_ARTICLE;
    }
    
    /**
     * @inheritDoc
     */
    public function getReferId()
    {
        return $this->ArticleCmtLIke->comment->article->id;
    }
    
    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->time;
    }
}
