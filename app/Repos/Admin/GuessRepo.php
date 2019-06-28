<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 14/04/2019
 * Time: 15:48
 */

namespace App\Repos\Admin;

use App\Models\Admin;
use App\Logics\Share\ShareLogic;
use App\Models\GoodsInfo;
use App\Models\GuessStage;
use App\Models\GuessGoods;
use App\Models\User;
use App\Models\Guess;
use App\Models\VipSendLog;
use App\Models\UserCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
class GuessRepo
{

    public function info($id)
    {
        /** @var UserExp $exp */
        $guessgoods = GuessGoods::query()->where('stage_id', $id)->get();
        return $guessgoods;
    }

    /**
     * @param Admin $admin
     * @param       $data
     */
    public function create(Admin $admin, $data)
    {
//        $video = Video::query()->create(array_filter(array_only($data, [
//            'author_id',
//            'title',
//            'cover',
//            'content',
//        ])));
//
//        $data = [
//            'author_id' => $admin->id,
//        ];
//
//        $video->update($data);
    }

    /**
     * @param                 $id
     * @param                 $data
     */
    public function update($id, $data)
    {
        $userexp = GuessGoods::query()->find($id);

        $userexp->update(array_only($data, [
            'goods_name',
            'goods_img',
            'goods_orderby',
            'number_level',
            'number_prefix'
        ]));

        $userexp->update($data);
    }

    /**
     * @param $query
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list($query, $offset = 0, $pageSize = 0)
    {
        $builder = GuessStage::query();

        $Total = $builder->count();
        //暂时考虑兼容性
        if(!empty($pageSize)) {
            $builder->offset($offset)->limit($pageSize);
        }
        $guessstage = $builder->get();

        //暂时考虑兼容性
        if(empty($pageSize)) {
            return $guessstage;
        }
        return ['total'=>$Total,'list'=>$guessstage];
    }


    /**
     * @param $query
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function tongji($id)
    {
        $res = \DB::select('select g.stage_id,g.goods_id,gg.goods_name,g.number,count(1) as total from xsw_guess g INNER JOIN xsw_guess_goods gg on g.goods_id=gg.id where g.stage_id = :id and g.user_number = 5 group by g.goods_id,g.number order by g.goods_id,total asc', [':id'=>$id]);
        return $res;
    }

    /**
     * @param $query
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function users($query, $offset = 0, $pageSize = 0)
    {
        $builder = Guess::query()
        ->with([
        'user'  => function ($q) {
            /** @var Builder $q */
            $q->select(['id', 'phone', 'nickname']);
        }
        ]);
        $builder->where('user_number', 5);
        if (!empty(array_get($query, 'goods_id'))) {
            $builder->where('goods_id', $query['goods_id']);
        }
        if (!empty(array_get($query, 'stage_id'))) {
            $builder->where('stage_id', $query['stage_id']);
        }
        if (!empty(array_get($query, 'number'))) {
            $builder->where('number', $query['number']);
        }
        $Total = $builder->count();
        //暂时考虑兼容性
        if(!empty($pageSize)) {
            $builder->offset($offset)->limit($pageSize);
        }
        $guess = $builder->get();

        //暂时考虑兼容性
        if(empty($pageSize)) {
            return $guess;
        }
        return ['total'=>$Total,'list'=>$guess];
    }
}
