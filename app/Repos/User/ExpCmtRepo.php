<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 15/04/2019
 * Time: 23:08
 */

namespace App\Repos\User;

use App\Events\Exp\CommentWasLiked;
use App\Events\Exp\ArticleWasCommented;
use App\Events\Exp\ExpWasCommented;
use App\Models\User;
use App\Models\UserExp;
use App\Models\UserExpCmt;
use App\Models\UserExpCmtLike;

class ExpCmtRepo
{
    
    /**
     * @param User $user
     * @param      $data
     */
    public function create(User $user, $data)
    {
        /** @var UserExp $exp */
        $exp = UserExp::query()->find($data['exp_id']);
        
        /** @var UserExpCmt $cmtModel */
        $cmtModel = UserExpCmt::query()->create(array_merge(array_only($data, [
            'exp_id',
            'content',
        ]), [
            'user_id'   => $user->id,
            'pid'       => array_get($data, 'pid', 0),
            'is_author' => $user->id == $exp->user_id,
        ]));
        
        event(new ExpWasCommented($cmtModel));
    }
    
    /**
     * @param User $user
     * @param      $id
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function like(User $user, $id)
    {
        try {
            \DB::beginTransaction();
            /** @var UserExpCmt $comment */
            $comment = UserExpCmt::query()->find($id);
            if (UserExpCmtLike::query()
                    ->where('user_id', '=', $user->id)
                    ->where('comment_id', $id)
                    ->count() === 0) {
                $comment->increment('like');
                
                /** @var UserExpCmtLike $likeModel */
                $likeModel = UserExpCmtLike::query()->create([
                    'user_id'    => $user->id,
                    'comment_id' => $id,
                ]);
                
                event(new CommentWasLiked($likeModel));
                
                \DB::commit();
                return json_response([], '点赞成功');
            } else {
                return json_response([], '您已经点过赞了哦~', 400);
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            return json_response([], '点赞失败了哦', 400);
        }
    }
    
    /**
     * @param User $user
     * @param      $id
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function unlike(User $user, $id)
    {
        try {
            \DB::beginTransaction();
            /** @var UserExpCmt $comment */
            $comment = UserExpCmt::query()->find($id);
            if (UserExpCmtLike::query()
                ->where('user_id', '=', $user->id)
                ->where('comment_id', $id)
                ->exists()) {
                $comment->decrement('like');
                UserExpCmtLike::query()->where('user_id', $user->id)
                    ->where('comment_id', $id)
                    ->delete();
                
                \DB::commit();
                return json_response([], '取消点赞成功');
            } else {
                return json_response([], '您已经取消点赞了哦~', 400);
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            return json_response([], '取消点赞失败了哦', 400);
        }
    }
    
    /**
     * @param      $id
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function list($id)
    {
        /** @var UserExp $exp */
        $exp = UserExp::query()->find($id);
        
        $comments = $exp->comments();
        
        $comments->transform(function ($c) {
            $getLikeStatus = function ($c) {
                $user = app('request')->user();
                if ($user) {
                    return UserExpCmtLike::query()
                        ->where('user_id', $user->id)
                        ->where('comment_id', $c->id)
                        ->exists();
                }
                return false;
            };
            /** @var UserExpCmt $c */
            $c['is_liked'] = $getLikeStatus($c);
            
            $c['children']->transform(function ($cld) use ($getLikeStatus) {
                /** @var UserExpCmt $cld */
                $cld['is_liked'] = $getLikeStatus($cld);
                return $cld;
            });
            
            return $c;
        });
        
        return $comments;
    }
}
