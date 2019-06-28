<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 24/04/2019
 * Time: 23:00
 */

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Logics\Share\ShareLogic;
use App\Models\Article;
use App\Models\User;
use App\Models\UserShare;
use App\Repos\Share\ShareRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Share extends Controller
{
    /**
     * 分享逻辑
     *
     * @param Request    $request
     *
     * @param ShareLogic $shareLogic
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, ShareLogic $shareLogic)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'target'   => [
                'required',
                'string',
                Rule::in(
                    UserShare::T_ARTICLE,
                    UserShare::T_EXP,
                    UserShare::T_GOODS
                ),
            ],
            'goods_id' => [
                'required',
                'int',
                Rule::exists('xsw_goods_info', 'id')
                    ->whereNull('deleted_at'),
            ],
        ]);
        
        switch ($request->input('target')) {
            case UserShare::T_ARTICLE:
                $this->validate($request, [
                    'target_id' => [
                        'required',
                        'int',
                        Rule::exists('xsw_article', 'id')
                            ->where('status', Article::S_PUBLISHED),
                    ],
                ]);
                break;
            case UserShare::T_EXP:
                $this->validate($request, [
                    'target_id' => [
                        'required',
                        'int',
                        Rule::exists('xsw_user_exp', 'id'),
                    ],
                ]);
                break;
            case UserShare::T_GOODS:
                $this->validate($request, [
                    'target_id' => [
                        'required',
                        'int',
                        Rule::exists('xsw_goods_info', 'id'),
                    ],
                ]);
                break;
        }
        
        
        $url = $shareLogic->createShare(
            $user,
            $request->input('target'),
            $request->input('target_id'),
            $request->input('goods_id')
        );
        $url2 = $shareLogic->createShareWap(
            $user,
            $request->input('target'),
            $request->input('target_id'),
            $request->input('goods_id')
        );
        return json_response([
            'jump_url' => $url,
            'share_url' => $url2,
        ]);
    }
    
    /**
     * personal share list(user exp list)
     *
     * @param Request   $request
     * @param ShareRepo $shareRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function list(Request $request, ShareRepo $shareRepo)
    {
        return json_response($shareRepo->list($request->user()));
    }
    
    /**
     * @param Request   $request
     * @param ShareRepo $shareRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function linkList(Request $request, ShareRepo $shareRepo)
    {
        return json_response($shareRepo->linkList($request->user()));
    }
    
    /**
     * @param Request   $request
     * @param ShareRepo $shareRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function income(Request $request, ShareRepo $shareRepo)
    {
        $totalIncome = $shareRepo->income($request->user());
        return json_response($totalIncome);
    }
    
    /**
     * @param Request   $request
     * @param ShareRepo $shareRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function withdrawInfo(Request $request, ShareRepo $shareRepo)
    {
        return json_response($shareRepo->withdrawInfo($request->user()));
    }
}
