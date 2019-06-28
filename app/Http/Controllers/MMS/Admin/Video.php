<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 14:41
 */

namespace App\Http\Controllers\MMS\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Video as VideoModel;
use App\Repos\Admin\VideoRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Video extends Controller
{
    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, VideoRepo $videoRepo)
    {
        $this->validate($request, [
            'title'       => 'required|string',
            'content'     => 'required|string',
        ]);

        $videoRepo->create($request->user(), $request->only([
            'author_id',
            'title',
            'cover',
            'content',
        ]));
        
        return json_response([], '操作成功');
    }
    
    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request, VideoRepo $videoRepo)
    {
        if(!empty($request->input('ispage'))) {
            $pageSize = empty($request->input('pageSize')) ? 10 : $request->input('pageSize');
            $pageIndex = empty($request->input('pageIndex')) ? 1 : $request->input('pageIndex');
            $offset = ceil($pageSize * ($pageIndex - 1));
            $videos = $videoRepo->list($request->input('query'), $offset, $pageSize);
        }else{
            $videos = $videoRepo->list($request->input('query'));
        }
        
        return json_response($videos);
    }
}
