<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 15/04/2019
 * Time: 23:07
 */

namespace App\Http\Controllers\Api\Discovery;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repos\User\ExpCmtRepo;
use Illuminate\Http\Request;

class ExpCmt extends Controller
{
    /**
     * @param Request    $request
     *
     * @param ExpCmtRepo $expCmtRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request, ExpCmtRepo $expCmtRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_user_exp',
        ]);
        
        $comments = $expCmtRepo->list($request->input('id'));
        
        return json_response($comments);
    }
    
    /**
     * @param Request $request
     *
     * @param ExpCmtRepo $expCmtRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, ExpCmtRepo $expCmtRepo)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'exp_id'  => 'required|int|exists:xsw_user_exp,id',
            'pid'     => 'int|exists:xsw_user_exp_comment,id',
            'content' => 'required|string|max:500',
        ]);
        
        $expCmtRepo->create($user, $request->only([
            'exp_id',
            'pid',
            'content',
        ]));
        
        return json_response([], '回复成功');
    }
    
    
    /**
     * @param Request $request
     *
     * @param ExpCmtRepo $expCmtRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function like(Request $request, ExpCmtRepo $expCmtRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_user_exp_comment,id',
        ]);
        
        return $expCmtRepo->like($request->user(), $request->input('id'));
    }
    
    /**
     * @param Request $request
     * @param ExpCmtRepo $expCmtRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function unlike(Request $request, ExpCmtRepo $expCmtRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_user_exp_comment,id',
        ]);
        
        return $expCmtRepo->unlike($request->user(), $request->input('id'));
    }
}
