<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 20:49
 */

namespace App\Http\Controllers\Api\Index;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repos\Index\ArticleCmtRepo;
use Illuminate\Http\Request;

class ArticleCmt extends Controller
{
    /**
     * @param Request        $request
     *
     * @param ArticleCmtRepo $articleCmtRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request, ArticleCmtRepo $articleCmtRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_article',
        ]);
        
        $comments = $articleCmtRepo->list($request->input('id'));
        
        return json_response($comments);
    }
    
    /**
     * @param Request        $request
     *
     * @param ArticleCmtRepo $articleCmtRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, ArticleCmtRepo $articleCmtRepo)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'article_id' => 'required|int|exists:xsw_article,id',
            'pid'        => 'int|exists:xsw_article_comment,id',
            'content'    => 'required|string|max:500',
        ]);
        
        $articleCmtRepo->create($user, $request->only([
            'article_id',
            'pid',
            'content',
        ]));
        
        return json_response([], '回复成功');
    }
    
    
    /**
     * @param Request        $request
     *
     * @param ArticleCmtRepo $articleCmtRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function like(Request $request, ArticleCmtRepo $articleCmtRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_article_comment,id',
        ]);
        
        return $articleCmtRepo->like($request->user(), $request->input('id'));
    }
    
    /**
     * @param Request        $request
     * @param ArticleCmtRepo $articleCmtRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function unlike(Request $request, ArticleCmtRepo $articleCmtRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_article_comment,id',
        ]);
        
        return $articleCmtRepo->unlike($request->user(), $request->input('id'));
    }
}
