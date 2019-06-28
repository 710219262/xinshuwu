<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 16:06
 */

namespace App\Http\Controllers\Api\Index;

use App\Events\Order\OrderWasReceived;
use App\Http\Controllers\Controller;
use App\Listeners\OrderWasReceivedListener;
use App\Models\MallHome as MallHomeModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Repos\Index\MallHomeRepo;

class MallHome extends Controller
{
    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request, MallHomeRepo $mallhomeRepo)
    {

        $pid = 0;
        $mallhome = $mallhomeRepo->list($pid);

        foreach ($mallhome as $k => $v){
            $mallhome[$k]['child'] = $mallhomeRepo->list($v['id']);
        }
        return json_response($mallhome);
    }

    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function slider(Request $request, MallHomeRepo $mallhomeRepo)
    {
        $mallhome = $mallhomeRepo->listapp(1);
        return json_response($mallhome);
    }
}