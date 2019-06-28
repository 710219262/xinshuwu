<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 04/04/2019
 * Time: 19:25
 */

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Feedback as FeedbackModel;
use App\Models\User;
use Illuminate\Http\Request;

class Feedback extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'content' => 'required|string',
            'mobile'  => 'string',
        ]);
        
        $data = $request->only(['content', 'mobile']);
        $data['user_id'] = $user->id;
        
        FeedbackModel::query()->create(
            $data
        );
        
        return json_response();
    }
}
