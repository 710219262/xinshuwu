<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 18/03/2019
 * Time: 21:57
 */

namespace App\Http\Controllers\Api\User;

use App\Events\User\UserWasFollowed;
use App\Http\Controllers\Controller;
use App\Models\User as UserModel;
use App\Models\UserFollow;
use App\Models\UserShare;
use App\Models\UserTag;
use App\Repos\Redis\UserAuthRepo;
use App\Repos\User\UserRepo;
use App\Repos\User\GuessRepo;
use App\Services\AuthService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Ixudra\Curl\Facades\Curl;

class Guess extends Controller
{

    public function __construct()
    {

    }

    public function detail(Request $request, GuessRepo $guessRepo)
    {
        $res = $guessRepo->detail();
        $res['top_note'] = '泰国六日游，清空购物车，4位数礼包，等你来挑战';
        $res['start_at'] = date('Y-m-d', strtotime($res['start_at']));
        $res['end_at'] = date('Y-m-d', strtotime($res['end_at']));
        $res['mid_note'] = array(
            array(
                'id' => 1,
                'note' =>'1.邀请5位好友组成小队，参与数字竞猜。组成小队即可获得38元月会员（不叠加）。可选择竞猜1位数/2位数/3位数。猜中即可获得对应的奖品。（组成5人团队，竞猜才有效，否侧视为无效竞猜）。'),
            array(
                'id' => 2,
                'note' =>'2.小队成员每人一次竞猜机会，重新选择对应奖品可以重新组队（每组队员不可以重复相同，每个奖品都有一次组队机会）。'),
            array(
                'id' => 3,
                'note' =>'3.点击奖品下面的数字按钮即可进入竞猜页面，分享就可以邀请队员。数字与奖品价格没有联系。')
        );
        $res['bottom_note'] = array(
            array(
                'id' => 1,
                'note' =>'1.开奖数字及中奖名单与7月4号下午6点前在“猩事物”商城首页公布，奖品会在7个工作日完成派送。'),
            array(
                'id' => 2,
                'note' =>'2.中奖人员需在3个工作日内填写相关信息（相关信息可在猩事物公众号填写），过期则视为自动放弃领取，请务必关注信息，以防大奖悄悄溜走。'),
            array(
                'id' => 3,
                'note' =>'3.本次活动最终解释权归猩事物所有，如有疑问请致电猩事物官方客服，咨询电话：400-0118891。')
        );
        $res['vip_img'] = array(
            array(
                'id' => 1,
                'img_name' =>'购买38元月度会员送价值48元手持风扇',
                'img_url' =>'https://static.xshiwu.com/uploads/12111.jpg'),
            array(
                'id' => 2,
                'img_name' =>'购买88元月度会员送价值108元榨汁机',
                'img_url' =>'https://static.xshiwu.com/uploads/2222.jpg'),
            array(
                'id' => 3,
                'img_name' =>'购买268元月度会员送价值288元圣罗兰口红',
                'img_url' =>'https://static.xshiwu.com/uploads/33333.jpg')
        );
        return json_response($res);
    }

    public function info(Request $request, GuessRepo $guessRepo)
    {
        if ($request->has('guess_no')) {
            $this->validate($request, [
                'guess_no'     => 'required|string|exists:xsw_guess,guess_no',
            ]);
        }
        else{
            $this->validate($request, [
                'stage_id'       => 'required|int',
                'goods_id'     => 'required|int',
            ]);
        }
        /** @var \App\Models\User $user */
        $user = $request->user();
        $res = $guessRepo->info($user, $request->only(['stage_id', 'goods_id', 'guess_no']));
        //$res->stage = $this->detail($request, $guessRepo);
        //unset($res['stage']['vip_img']);
        //unset($res['stage']['bottom_note']);
        $res_stage = $guessRepo->detail();
        $res_stage['top_note'] = '泰国六日游，清空购物车，4位数礼包，等你来挑战';
        $res_stage['start_at'] = date('Y-m-d', strtotime($res_stage['start_at']));
        $res_stage['end_at'] = date('Y-m-d', strtotime($res_stage['end_at']));
        $res_stage['mid_note'] = array(
            array(
                'id' => 1,
                'note' =>'1.邀请5位好友组成小队，参与数字竞猜。组成小队即可获得38元月会员（不叠加）。可选择竞猜1位数/2位数/3位数。猜中即可获得对应的奖品。（组成5人团队，竞猜才有效，否侧视为无效竞猜）。'),
            array(
                'id' => 2,
                'note' =>'2.小队成员每人一次竞猜机会，重新选择对应奖品可以重新组队（每组队员不可以重复相同，每个奖品都有一次组队机会）。'),
            array(
                'id' => 3,
                'note' =>'3.点击奖品下面的数字按钮即可进入竞猜页面，分享就可以邀请队员。数字与奖品价格没有联系。')
        );
        $res_stage['bottom_note'] = array(
            array(
                'id' => 1,
                'note' =>'1.开奖数字及中奖名单与7月4号下午6点前在“猩事物”商城首页公布，奖品会在7个工作日完成派送。'),
            array(
                'id' => 2,
                'note' =>'2.中奖人员需在3个工作日内填写相关信息（相关信息可在猩事物公众号填写），过期则视为自动放弃领取，请务必关注信息，以防大奖悄悄溜走。'),
            array(
                'id' => 3,
                'note' =>'3.本次活动最终解释权归猩事物所有，如有疑问请致电猩事物官方客服，咨询电话：400-0118891。')
        );
        $res['stage'] = $res_stage;
        return json_response($res);
    }

    public function app_info(Request $request, GuessRepo $guessRepo)
    {
        $this->validate($request, [
            'stage_id'       => 'required|int',
            'goods_id'     => 'required|int',
            'user_id'     => 'required|int',
        ]);
        /** @var \App\Models\User $user */
        $res = $guessRepo->app_info($request['user_id'], $request->only(['stage_id', 'goods_id', 'guess_no']));
        //$res->stage = $this->detail($request, $guessRepo);
        //unset($res['stage']['vip_img']);
        //unset($res['stage']['bottom_note']);
        $res_stage = $guessRepo->detail();
        $res_stage['top_note'] = '出国五日游？清空购物车？4位礼包数？敢猜你就来';
        $res_stage['start_at'] = date('Y-m-d', strtotime($res_stage['start_at']));
        $res_stage['end_at'] = date('Y-m-d', strtotime($res_stage['end_at']));
        $res_stage['mid_note'] = array(
            array(
                'id' => 1,
                'note' =>'1.邀请5位好友，组成小队，参与数字竞猜活动，可免费获得价38元一个月会员，猜中即可获得2位数/3位数/4位数的豪华奖品;'),
            array(
                'id' => 2,
                'note' =>'2.小队成员每人一次竞猜机会，分享给其他朋友，重组队可再获得一次竞猜机会;（每组队员不可重复相同，组队次数不限）'),
            array(
                'id' => 3,
                'note' =>'3.数字和对应产品价格没有联系。')
        );
        $res_stage['bottom_note'] = array(
            array(
                'id' => 1,
                'note' =>'1.正确数字及猜中名单将于每周五17:00在“猩事物”APP首页公布，奖品则在7个工作日内寄出；'),
            array(
                'id' => 2,
                'note' =>'2.中奖人员需在3个工作日内填写相关信息，过期则视为自动放弃领取请务必留意“猩事物‘内相关信息；'),
            array(
                'id' => 3,
                'note' =>'3.本次活动最终解释权归猩事物所有，如有疑问请致电猩事物官方客服，咨询电话：400-0118891；')
        );
        $res['stage'] = $res_stage;
        return json_response($res);
    }

    public function app_create(Request $request, GuessRepo $guessRepo)
    {
        $this->validate($request, [
            'stage_id'       => 'required|int',
            'goods_id'     => 'required|int',
            'number'     => 'required|int',
            'user_id'     => 'required|int',
        ]);
        return  $guessRepo->app_create($request['user_id'], $request->only(['stage_id', 'goods_id', 'guess_no', 'number']));
    }

    public function create(Request $request, GuessRepo $guessRepo)
    {
        if ($request->has('guess_no')) {
            $this->validate($request, [
                'guess_no'     => 'required|string|exists:xsw_guess,guess_no',
                'number'     => 'required|int',
            ]);
        }
        else{
            $this->validate($request, [
                'stage_id'       => 'required|int',
                'goods_id'     => 'required|int',
                'number'     => 'required|int',
            ]);
        }
        /** @var \App\Models\User $user */
        $user = $request->user();
        return  $guessRepo->create($user, $request->only(['stage_id', 'goods_id', 'guess_no', 'number']));
    }

    public function guessno(Request $request, GuessRepo $guessRepo)
    {
        $this->validate($request, [
            'stage_id'       => 'required|int',
            'goods_id'     => 'required|int',
        ]);
        /** @var \App\Models\User $user */
        $user = $request->user();
        $res = $guessRepo->info2($user, $request->only(['stage_id', 'goods_id']));

        if(empty($res)){
            $res=["id"=>"","stage_id"=>$request['stage_id'],"goods_id"=>$request['goods_id'],"guess_no"=>""];
        }
        return json_response($res);
    }
    public function guessno2(Request $request, GuessRepo $guessRepo)
    {
        $this->validate($request, [
            'stage_id'       => 'required|int',
            'goods_id'     => 'required|int',
            'user_id'     => 'required|int',
        ]);
        $res = $guessRepo->info3($request['user_id'], $request->only(['stage_id', 'goods_id']));
        if(empty($res)){
            $res=["id"=>"","stage_id"=>$request['stage_id'],"goods_id"=>$request['goods_id'],"guess_no"=>""];
        }
        return json_response($res);
    }
}


