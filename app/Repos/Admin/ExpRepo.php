<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 14/04/2019
 * Time: 15:48
 */

namespace App\Repos\Admin;

use App\Events\Exp\ExpWasCollected;
use App\Events\Exp\ExpWasLiked;
use App\Logics\Share\ShareLogic;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\User;
use App\Models\UserCollection;
use App\Models\UserExp;
use App\Models\UserExpLike;
use App\Models\UserExpMedia;
use App\Models\UserShare;
use Illuminate\Database\Eloquent\Builder;

class ExpRepo
{
    
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list($query = [], $offset = 0, $pageSize = 0)
    {
        $builder = UserExp::query();
        if (!empty(array_get($query, 'status'))) {
            $query['status'] = [$query['status']];//转数组
            $builder->whereIn('status', $query['status']);
        }
        if (!empty(array_get($query, 'goods_id'))) {
            $builder->where('goods_id', $query['goods_id']);
        }
        if (!empty(array_get($query, 'user_id'))) {
            $builder->where('user_id', $query['user_id']);
        }
        $Total = $builder->count();

        //暂时考虑兼容性
        if(!empty($pageSize)) {
            $builder->offset($offset)->limit($pageSize);
        }

        $exps = $builder
            ->orderBy('id', 'DESC')->select([
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

        //暂时考虑兼容性
        if(empty($pageSize)) {
            return $exps;
        }
        return ['total'=>$Total,'list'=>$exps];
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
        return $exp;
    }

    /**
     * @param                 $id
     * @param                 $data
     */
    public function update($id, $data)
    {
        $userexp = UserExp::query()->find($id);

        if(!empty($data['deleted_at'])) $data['deleted_at'] = date("Y-m-d H:i:s",$data['deleted_at']);
        $userexp->update(array_only($data, [
            'status',
            'reject_reason',
            'deleted_at'
        ]));

        $userexp->update($data);
    }
}
