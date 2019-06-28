<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 14:52
 */

namespace App\Repos\Admin;

use App\Models\Admin;
use App\Models\User;

class UserRepo
{

    
    /**
     * @param $query
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list($query, $offset = 0, $pageSize = 0)
    {
        $builder = User::query();
        if ($phone = array_get($query, 'phone')) {
            $builder->where('phone', 'like', '%'.$phone.'%');
        }
        $Total = $builder->count();
        //暂时考虑兼容性
        if(!empty($pageSize)) {
            $builder->offset($offset)->limit($pageSize);
        }
        $users = $builder->orderBy('id', 'desc')->get();

        //暂时考虑兼容性
        if(empty($pageSize)) {
            return $users;
        }
        return ['total'=>$Total,'list'=>$users];
    }
}
