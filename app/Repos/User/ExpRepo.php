<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 14/04/2019
 * Time: 15:48
 */

namespace App\Repos\User;

use App\Events\Exp\ExpWasCollected;
use App\Events\Exp\ExpWasLiked;
use App\Logics\Share\ShareLogic;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\User;
use App\Models\UserCollection;
use App\Models\UserExp;
use App\Models\UserExp as UserExpModel;
use App\Models\UserExpLike;
use App\Models\UserExpMedia;
use App\Models\UserShare;
use Illuminate\Database\Eloquent\Builder;

class ExpRepo
{
    /**
     * @param User $user
     * @param      $data
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function create(User $user, $data)
    {
        
        try {
            \DB::beginTransaction();
            
            $orderGoodsId = array_get($data, 'id');
            /** @var OrderGoods $orderGoods */
            $orderGoods = OrderGoods::query()->find($orderGoodsId);
            
            //Order status changed to SHARED
            Order::query()->where('order_no', $orderGoods->order_no)
                ->whereIn('status', [Order::S_RECEIVED])
                ->update([
                    'status' => Order::S_SHARED,
                ]);
            
            /** @var UserExp $exp */
            $exp = UserExp::query()->create([
                'user_id'        => $user->id,
                'goods_id'       => $orderGoods->goods_id,
                'sku_id'         => $orderGoods->sku_id,
                'order_goods_id' => $orderGoodsId,
                'title'          => array_get($data, 'title'),
                'content'        => array_get($data, 'content'),
            ]);
            
            $medias = array_get($data, 'media');
            
            foreach ($medias as $media) {
                UserExpMedia::query()->create([
                    'user_id' => $user->id,
                    'exp_id'  => $exp->id,
                    'url'     => $media['url'],
                    'type'    => $media['type'],
                    'height'  => $media['height'],
                    'width'   => $media['width'],
                ]);
            }

            /** @var ShareLogic $shareLogic */
            $shareLogic = app(ShareLogic::class);

            $shareLogic->createShare(
                $user,
                UserShare::T_EXP,
                $exp->id,
                $orderGoods->goods_id
            );
            
            \DB::commit();
            return json_response([], '发布成功');
        } catch (\Exception $e) {
            \DB::rollBack();
            return json_response([], "发体验失败了哦~");
        }
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list()
    {
        $builder = UserExp::query();
        $builder->whereIn('status', [UserExp::S_COMPLETED]);
        return $builder->orderBy('id', 'DESC')->select([
            'id',
            'user_id',
            'goods_id',
            'title',
            'content',
            'like',
            'view',
            'collect',
            'status',
            'reject_reason',
        ])->with([
            'user'  => function ($q) {
                /** @var Builder $q */
                $q->select(['id', 'avatar', 'nickname']);
            },
            'goods' => function ($q) {
                /** @var Builder $q */
                $q->select(['id', 'name', 'price']);
            },
        ])->get();
    }
    
    public function info($id)
    {
        /** @var UserExp $exp */
        $exp = UserExp::query()->with([
            'user'  => function ($q) {
                /** @var Builder $q */
                $q->select(['id', 'avatar', 'nickname']);
            },
            'goods' => function ($q) {
                /** @var Builder $q */
                $q->select(['id', 'name', 'price']);
            },
        ])->where('id', $id)->first([
            'id',
            'user_id',
            'goods_id',
            'title',
            'content',
            'like',
            'view',
            'collect',
            'status',
            'reject_reason',
        ]);
        /** @var UserShare $share */
        $share    = UserShare::query()
            ->where('target', UserShare::T_EXP)
            ->where('target_id', $id)
            ->where('user_id', $exp->user_id)
            ->first();
        $exp->aff = $share ? $share->aff : '';
        UserExp::query()
            ->where('id', $id)
            ->increment('view');
        return $exp;
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
            /** @var UserExp $exp */
            $exp = UserExp::query()->find($id);
            if (UserExpLike::query()
                    ->where('user_id', '=', $user->id)
                    ->where('exp_id', $id)
                    ->count() === 0) {
                $exp->increment('like');
                
                /** @var UserExpLike $likeModel */
                $likeModel = UserExpLike::query()->create([
                    'user_id' => $user->id,
                    'exp_id'  => $id,
                ]);
                
                event(new ExpWasLiked($likeModel));
                
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
            /** @var UserExp $exp */
            $exp = UserExp::query()->find($id);
            if (UserExpLike::query()
                ->where('user_id', '=', $user->id)
                ->where('exp_id', $id)
                ->exists()) {
                $exp->decrement('like');
                
                UserExpLike::query()->where('user_id', $user->id)
                    ->where('exp_id', $id)
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
     * @param User $user
     * @param      $id
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function collect(User $user, $id)
    {
        if (UserCollection::query()->where('type', UserCollection::T_EXP)
                ->where('collect_id', $id)
                ->where('user_id', $user->id)
                ->count() === 0) {
            /** @var UserCollection $collectModel */
            $collectModel = UserCollection::query()->create([
                'type'       => UserCollection::T_EXP,
                'collect_id' => $id,
                'user_id'    => $user->id,
            ]);
            
            UserExp::query()
                ->where('id', $id)
                ->increment('collect');
            
            event(new ExpWasCollected($collectModel));
        }
        
        
        return json_response([], '收藏成功');
    }
}
