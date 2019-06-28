<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 14:52
 */

namespace App\Repos\Admin;

use App\Models\Admin;
use App\Models\Video;

class VideoRepo
{
    /**
     * @param Admin $admin
     * @param       $data
     */
    public function create(Admin $admin, $data)
    {
        $video = Video::query()->create(array_filter(array_only($data, [
            'author_id',
            'title',
            'cover',
            'content',
        ])));
        
        $data = [
            'author_id' => $admin->id,
        ];

        $video->update($data);
    }

    
    /**
     * @param $query
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list($query, $offset = 0, $pageSize = 0)
    {
        $builder = Video::query();

        $Total = $builder->count();
        //暂时考虑兼容性
        if(!empty($pageSize)) {
            $builder->offset($offset)->limit($pageSize);
        }
        $videos = $builder->get();

        //暂时考虑兼容性
        if(empty($pageSize)) {
            return $videos;
        }
        return ['total'=>$Total,'list'=>$videos];
    }
}
