<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 14:52
 */

namespace App\Repos\Admin;

use App\Models\Admin;
use App\Models\UserTransaction;
use App\Models\MerchantTransaction;
use App\Models\PlatformTransaction;
use Illuminate\Database\Eloquent\Builder;

class TransactionRepo
{
    /**
     * @param $query
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function userlist($query, $offset = 0, $pageSize = 0)
    {
        $builder = UserTransaction::query()
        ->with([
        'user'  => function ($q) {
            /** @var Builder $q */
            $q->select(['id', 'phone', 'avatar', 'nickname']);
        }
    ]);
        $Total = $builder->count();
        //暂时考虑兼容性
        if(!empty($pageSize)) {
            $builder->offset($offset)->limit($pageSize);
        }
        $videos = $builder->orderBy('id', 'desc')->get();

        //暂时考虑兼容性
        if(empty($pageSize)) {
            return $videos;
        }
        return ['total'=>$Total,'list'=>$videos];
    }
}
