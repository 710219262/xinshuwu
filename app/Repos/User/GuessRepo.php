<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 14/04/2019
 * Time: 15:48
 */

namespace App\Repos\User;

use App\Logics\Share\ShareLogic;
use App\Models\GoodsInfo;
use App\Models\GuessStage;
use App\Models\GuessGoods;
use App\Models\User;
use App\Models\Guess;
use App\Models\VipSendLog;
use App\Models\UserCollection;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Services\SmsService;
class GuessRepo
{


    /**
     * @param $query
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function detail()
    {
        $stage = GuessStage::query()
            ->first()
            ->append([
                'goods',
            ]);
        return $stage;
    }

    public function info(User $user, array $data)
    {
        //他人进来
        if (!empty($data['guess_no'])) {
            $guess = Guess::query()->where('user_id', $user->id)
                ->where('guess_no', $data['guess_no'])
                ->first();
            if(empty($guess)) {
                //查找创建者
                $guess_create = Guess::query()->where('is_create', 1)
                    ->where('guess_no', $data['guess_no'])
                    ->first();
                $guess = $data;
                $guess['stage_id'] = $guess_create['stage_id'];
                $guess['goods_id'] = $guess_create['goods_id'];
                $guess['user_number'] = $guess_create['user_number'];
                $guess['number'] = '';
                $guess['user_id'] = $guess_create['user_id'];
                $guess['guess_no'] = $guess_create['guess_no'];
                $guess['goods'] = GuessGoods::query()->where('id', $guess_create['goods_id'])->first();

                $user = User::query()->where('id', $user->id)
                    ->first(['id', 'avatar', 'nickname']);
                $guess['user'] = $user;
				$user_create = User::query()->where('id', $guess_create['user_id'])
                    ->first(['id', 'avatar', 'nickname']);
                $guess['team_name'] = $user_create['nickname'] . '的小队';
                $guess_all = Guess::query()->with([
                    'user' => function ($q) {
                        $q->select(['id', 'avatar', 'nickname']);
                    }
                ])->where('guess_no', $guess['guess_no'])
                    ->orderBy('is_create', 'desc')
                    ->get();
                $guess['all'] = $guess_all;
            }
            else{
				//查找创建者
                $guess_create = Guess::query()->where('is_create', 1)
                    ->where('guess_no', $data['guess_no'])
                    ->first();
                $guess = Guess::query()->with([
                    'user' => function ($q) {
                        $q->select(['id', 'avatar', 'nickname']);
                    }])->where('user_id', $user->id)
                    ->where('guess_no', $data['guess_no'])
                    ->first();
                $guess['goods'] = GuessGoods::query()->where('id', $guess['goods_id'])->first();
                $guess_all = Guess::query()->with([
                    'user'  => function ($q) {
                        $q->select(['id', 'avatar', 'nickname']);
                    }
                ])->where('guess_no', $guess['guess_no'])
                    ->orderBy('is_create','desc')
                    ->get();
					
				$user_create = User::query()->where('id', $guess_create['user_id'])
                    ->first(['id', 'avatar', 'nickname']);
                $guess['team_name'] = $user_create['nickname'] . '的小队';
                $guess['all'] = $guess_all;
            }
        }else{
            $guess = Guess::query()->where('user_id', $user->id)
                ->where('stage_id', $data['stage_id'])
                ->where('goods_id', $data['goods_id'])
                ->where('is_create', 1)
                ->first();
            if(empty($guess)) {
                $guess = $data;
                $guess['guess_no'] = '';
                $guess['user_number'] = 0;
                $guess['number'] = '';
                $guess['user_id'] = $user->id;
                $guess['goods'] = GuessGoods::query()->where('id', $data['goods_id'])->first();
                $user = User::query()->where('id', $user->id)
                    ->first(['id', 'avatar', 'nickname']);
                $guess['user'] = $user;
                $guess['team_name'] = $guess['user']['nickname'] . '的小队';
                $guess['all'] = [];
            }
            else{
                $guess = Guess::query()->with([
                    'user' => function ($q) {
                        $q->select(['id', 'avatar', 'nickname']);
                    }])->where('user_id', $user->id)
                    ->where('stage_id', $data['stage_id'])
                    ->where('goods_id', $data['goods_id'])
                    ->where('is_create', 1)
                    ->first();
                $guess['goods'] = GuessGoods::query()->where('id', $guess['goods_id'])->first();
                $guess_all = Guess::query()->with([
                    'user'  => function ($q) {
                        $q->select(['id', 'avatar', 'nickname']);
                    }
                    ])->where('guess_no', $guess['guess_no'])
                        ->orderBy('is_create','desc')
                        ->get();
                $guess['team_name'] = $guess['user']['nickname'] . '的小队';
                $guess['all'] = $guess_all;
            }
        }
        return $guess;
    }

    public function create(User $user, array $data) {
        if (!empty($data['guess_no'])) {
            //查找创建者
            $guess_create = Guess::query()->where('is_create', 1)
                ->where('guess_no', $data['guess_no'])
                ->first();
            //是否被邀请
            $guess_already = Guess::query()->where('is_create', 0)
                ->where('user_id', $user->id)
                ->where('creater_id', $guess_create['user_id'])
                ->first();
            if (!empty($guess_already)){
                return json_response([], "此用户的竞猜您已经被邀请参与过竞猜了哦",401);
            }

            //是否过期
            $guess_stage = GuessStage::query()
                ->where('id', $guess_create['stage_id'])
                ->first();
            $now = strtotime(Carbon::now());
            if ($now > strtotime($guess_stage['end_at']) || $now < strtotime($guess_stage['start_at'])) {
                return json_response([], "竞猜未开始或者已过期",401);
            }
            if ($guess_create->user_number  < 5) {
                //添加记录
                $info['user_id'] = $user->id;
                $info['is_create'] = 0;
                $info['stage_id'] = $guess_create['stage_id'];
                $info['goods_id'] = $guess_create['goods_id'];
                $info['user_number'] = 1;
                $info['number'] = $data['number'];;
                $info['guess_no'] = $data['guess_no'];
                $info['creater_id'] = $guess_create['user_id'];
                Guess::query()->create($info);
                //改人数
                Guess::query()
                    ->where('guess_no', $data['guess_no'])
                    ->update([
                        'user_number' => $guess_create['user_number'] + 1,
                    ]);
                $sendlog = VipSendLog::query()->where('user_id', $guess_create['user_id'])->first();
                if ($guess_create->user_number  == 4 && empty($sendlog)) {
                    //送会员发短信
                    \App\Repos\User\UserVipOrder::send($guess_create['user_id'], 'MONTH');
                    $creater = User::query()->where('id', $guess_create['user_id'])->first()->toArray();
                    SmsService::sendVip(
                        $creater['phone']
                    );
                }
                return json_response([], "恭喜你，竞猜成功！");
            }
            else{
                return json_response([], "此小队已经满了哦",401);
            }
        }else{
            //是否过期
            $guess_stage = GuessStage::query()
                ->where('id', $data['stage_id'])
                ->first();
            $now = strtotime(Carbon::now());
            if ($now > strtotime($guess_stage['end_at']) || $now< strtotime($guess_stage['start_at'])) {
                return json_response([], "竞猜未开始或者已过期",401);
            }
            $guess_already = Guess::query()->where('is_create', 1)
                ->where('user_id', $user->id)
                ->where('stage_id', $data['stage_id'])
                ->where('goods_id', $data['goods_id'])
                ->first();
            if (!empty($guess_already)){
                return json_response([], "您已经组建小队并竞猜过了",401);
            }
            //添加记录
            $info['user_id'] = $user->id;
            $info['creater_id'] = $user->id;
            $info['is_create'] = 1;
            $info['stage_id'] =$data['stage_id'];
            $info['goods_id'] = $data['goods_id'];
            $guess_no = Guess::newOrderNum();
            $info['user_number'] = 1;
            $info['number'] = $data['number'];;
            $info['guess_no'] = $guess_no;
            $res = Guess::query()->create($info);
            return json_response($res, "恭喜你，竞猜成功！");
        }
    }

    public function app_info($user_id, array $data)
    {
        //他人进来
        if (!empty($data['guess_no'])) {
            $guess = Guess::query()->where('user_id', $user_id)
                ->where('guess_no', $data['guess_no'])
                ->first();
            if(empty($guess)) {
                //查找创建者
                $guess_create = Guess::query()->where('is_create', 1)
                    ->where('guess_no', $data['guess_no'])
                    ->first();
                $guess = $data;
                $guess['stage_id'] = $guess_create['stage_id'];
                $guess['goods_id'] = $guess_create['goods_id'];
                $guess['user_number'] = $guess_create['user_number'];
                $guess['number'] = '';
                $guess['user_id'] = $guess_create['user_id'];
                $guess['guess_no'] = $guess_create['guess_no'];
                $guess['goods'] = GuessGoods::query()->where('id', $guess_create['goods_id'])->first();

                $user = User::query()->where('id', $user_id)
                    ->first(['id', 'avatar', 'nickname']);
                $guess['user'] = $user;
                $guess['team_name'] = $guess['user']['nickname'] . '的小队';
                $guess_all = Guess::query()->with([
                    'user' => function ($q) {
                        $q->select(['id', 'avatar', 'nickname']);
                    }
                ])->where('guess_no', $guess['guess_no'])
                    ->orderBy('is_create', 'desc')
                    ->get();
                $guess['all'] = $guess_all;
            }
            else{
                $guess = Guess::query()->with([
                    'user' => function ($q) {
                        $q->select(['id', 'avatar', 'nickname']);
                    }])->where('user_id', $user_id)
                    ->where('guess_no', $data['guess_no'])
                    ->first();
                $guess['goods'] = GuessGoods::query()->where('id', $guess['goods_id'])->first();
                $guess_all = Guess::query()->with([
                    'user'  => function ($q) {
                        $q->select(['id', 'avatar', 'nickname']);
                    }
                ])->where('guess_no', $guess['guess_no'])
                    ->orderBy('is_create','desc')
                    ->get();
                $guess['team_name'] = $guess['user']['nickname'] . '的小队';
                $guess['all'] = $guess_all;
            }
        }else{
            $guess = Guess::query()->where('user_id', $user_id)
                ->where('stage_id', $data['stage_id'])
                ->where('goods_id', $data['goods_id'])
                ->first();
            if(empty($guess)) {
                $guess = $data;
                $guess['guess_no'] = '';
                $guess['user_number'] = 0;
                $guess['number'] = '';
                $guess['user_id'] = $user_id;
                $guess['goods'] = GuessGoods::query()->where('id', $data['goods_id'])->first();
                $user = User::query()->where('id', $user_id)
                    ->first(['id', 'avatar', 'nickname']);
                $guess['user'] = $user;
                $guess['team_name'] = $guess['user']['nickname'] . '的小队';
                $guess['all'] = [];
            }
            else{
                $guess = Guess::query()->with([
                    'user' => function ($q) {
                        $q->select(['id', 'avatar', 'nickname']);
                    }])->where('user_id', $user_id)
                    ->where('stage_id', $data['stage_id'])
                    ->where('goods_id', $data['goods_id'])
                    ->first();
                $guess['goods'] = GuessGoods::query()->where('id', $guess['goods_id'])->first();
                $guess_all = Guess::query()->with([
                    'user'  => function ($q) {
                        $q->select(['id', 'avatar', 'nickname']);
                    }
                ])->where('guess_no', $guess['guess_no'])
                    ->orderBy('is_create','desc')
                    ->get();
                $guess['team_name'] = $guess['user']['nickname'] . '的小队';
                $guess['all'] = $guess_all;
            }
        }
        return $guess;
    }

    public function app_create($user_id, array $data) {
        if (!empty($data['guess_no'])) {
            //是否被邀请
            $guess_already = Guess::query()->where('is_create', 0)
                ->where('user_id', $user_id)
                ->first();
            if (!empty($guess_already)){
                return json_response([], "您已经被邀请参与过竞猜了哦",401);
            }
            //查找创建者
            $guess_create = Guess::query()->where('is_create', 1)
                ->where('guess_no', $data['guess_no'])
                ->first();
            //是否过期
            $guess_stage = GuessStage::query()
                ->where('id', $guess_create['stage_id'])
                ->first();
            $now = strtotime(Carbon::now());
            if ($now > strtotime($guess_stage['end_at']) || $now < strtotime($guess_stage['start_at'])) {
                return json_response([], "竞猜未开始或者已过期",401);
            }
            if ($guess_create->user_number  < 5) {
                //添加记录
                $info['user_id'] = $user_id;
                $info['is_create'] = 0;
                $info['stage_id'] = $guess_create['stage_id'];
                $info['goods_id'] = $guess_create['goods_id'];
                $info['user_number'] = 1;
                $info['number'] = $data['number'];;
                $info['guess_no'] = $data['guess_no'];
                Guess::query()->create($info);
                //改人数
                Guess::query()
                    ->where('guess_no', $data['guess_no'])
                    ->update([
                        'user_number' => $guess_create['user_number'] + 1,
                    ]);
                $sendlog = VipSendLog::query()->where('user_id', $guess_create['user_id'])->first();
                if ($guess_create->user_number  == 4 && empty($sendlog)) {
                    \App\Repos\User\UserVipOrder::send($guess_create['user_id'], 'MONTH');
                }
                return json_response([], "恭喜你，竞猜成功！");
            }
            else{
                return json_response([], "此小队已经满了哦",401);
            }
        }else{
            //是否过期
            $guess_stage = GuessStage::query()
                ->where('id', $data['stage_id'])
                ->first();
            $now = strtotime(Carbon::now());
            if ($now > strtotime($guess_stage['end_at']) || $now< strtotime($guess_stage['start_at'])) {
                return json_response([], "竞猜未开始或者已过期",401);
            }
            $guess_already = Guess::query()->where('is_create', 1)
                ->where('user_id', $user_id)
                ->where('stage_id', $data['stage_id'])
                ->where('goods_id', $data['goods_id'])
                ->first();
            if (!empty($guess_already)){
                return json_response([], "您已经组建小队并竞猜过了",401);
            }
            //添加记录
            $info['user_id'] = $user_id;
            $info['is_create'] = 1;
            $info['stage_id'] =$data['stage_id'];
            $info['goods_id'] = $data['goods_id'];
            $guess_no = Guess::newOrderNum();
            $info['user_number'] = 1;
            $info['number'] = $data['number'];;
            $info['guess_no'] = $guess_no;
            $res = Guess::query()->create($info);
            return json_response($res, "恭喜你，竞猜成功！");
        }
    }

    public function info2(User $user, array $data)
    {
        $guess = Guess::query()->where('user_id', $user->id)
            ->where('stage_id', $data['stage_id'])
            ->where('goods_id', $data['goods_id'])
            ->where('is_create', 1)
            ->first([
                'id',
                'stage_id',
                'goods_id',
                'guess_no'
            ]);
        //查找创建者
        $guess_create = Guess::query()->where('is_create', 1)
            ->where('guess_no', $guess['guess_no'])
            ->first();
        $guess['goods'] = GuessGoods::query()->where('id', $guess_create['goods_id'])->first();
        $user = User::query()->where('id', $guess_create['user_id'])
            ->first(['id', 'avatar', 'nickname']);
        $guess['user'] = $user;
        $guess['team_name'] = $guess['user']['nickname'] . '的小队';
        return $guess;
    }

    public function info3($user_id, array $data)
    {
        $guess = Guess::query()->where('user_id', $user_id)
            ->where('stage_id', $data['stage_id'])
            ->where('goods_id', $data['goods_id'])
            ->first([
                'id',
                'stage_id',
                'goods_id',
                'guess_no'
            ]);
        //查找创建者
        $guess_create = Guess::query()->where('is_create', 1)
            ->where('guess_no', $guess['guess_no'])
            ->first();
        $guess['goods'] = GuessGoods::query()->where('id', $guess_create['goods_id'])->first();
        $user = User::query()->where('id', $guess_create['user_id'])
            ->first(['id', 'avatar', 'nickname']);
        $guess['user'] = $user;
        $guess['team_name'] = $guess['user']['nickname'] . '的小队';
        return $guess;
    }

}
