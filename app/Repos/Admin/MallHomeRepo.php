<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 14:52
 */

namespace App\Repos\Admin;

use App\Models\Admin;
use App\Models\MallHome;

class MallHomeRepo
{
    
    /**
     * @param $query
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list($pid)
    {
        $builder = MallHome::query();
            $builder->where('pid', $pid);
        $builder->orderBy('id', 'asc');
        $mallhome = $builder->get();
        
        return $mallhome;
    }

    /**
     * @param                 $id
     * @param                 $data
     */
    public function update($id, $data)
    {
        $mallhome = MallHome::query()->find($id);

        $mallhome->update(array_only($data, [
            'cover',
            'content',
            'appurl',
            'name',
            'price',
        ]));

        $mallhome->update($data);
    }
}
