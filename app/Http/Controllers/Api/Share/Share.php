<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 26/04/2019
 * Time: 21:50
 */

namespace App\Http\Controllers\Api\Share;

use App\Http\Controllers\Controller;
use App\Logics\Share\ShareLogic;
use App\Models\Article;
use App\Models\UserShare;
use App\Repos\Share\ShareRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Share extends Controller
{
    /**
     * @param Request    $request
     *
     * @param ShareLogic $shareLogic
     *
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function jump(Request $request, ShareLogic $shareLogic)
    {
        // u jump i'll not jump
        return $shareLogic->jack($request->input('t'));
    }
    
    /**
     * @param Request   $request
     *
     * @param ShareRepo $shareRepo
     *
     * @return ShareRepo
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getRank(Request $request, ShareRepo $shareRepo)
    {
        $this->validate($request, [
            'target' => [
                'required',
                'string',
                Rule::in(
                    UserShare::T_ARTICLE,
                    UserShare::T_EXP,
                    UserShare::T_GOODS
                ),
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
        
        
        return json_response($shareRepo->getRank(
            $request->input('target'),
            $request->input('target_id')
        ));
    }
}
