<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 27/04/2019
 * Time: 01:26
 */

namespace App\Repos\Share;

use App\Models\User;
use App\Models\UserExp;
use App\Models\UserShare;
use App\Models\UserTransaction;
use Illuminate\Database\Query\Builder;

class ShareRepo
{
    public function getRank($target, $targetId)
    {
        return UserShare::query()->with([
            'user' => function ($q) {
                /** @var Builder $q */
                $q->select([
                    'id',
                    'nickname',
                    'avatar',
                ]);
            },
        ])->where('target_id', $targetId)
            ->where('target', $target)
            ->where('income', '>', 0)
            ->orderBy('income', 'desc')
            ->select([
                'id',
                'user_id',
                'income',
                'created_at',
            ])->get();
    }
    
    /**
     * @param User $user
     *
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list(User $user)
    {
        return UserExp::query()->from('xsw_user_exp')->leftJoin(
            'xsw_user_share as share',
            'xsw_user_exp.id',
            '=',
            'share.target_id'
        )->select([
            'xsw_user_exp.id',
            'xsw_user_exp.user_id',
            'xsw_user_exp.goods_id',
            'xsw_user_exp.title',
            'xsw_user_exp.content',
            'xsw_user_exp.like',
            'xsw_user_exp.view',
            'xsw_user_exp.collect',
            'xsw_user_exp.created_at',
            'xsw_user_exp.income',
            'status',
            'reject_reason',
            \DB::raw('count(share.id) as share'),
        ])->where('xsw_user_exp.user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->groupBy('xsw_user_exp.id')->get();
    }

    /**
     * @param User $user
     *
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list_to_other(User $user)
    {
        return UserExp::query()->from('xsw_user_exp')->leftJoin(
            'xsw_user_share as share',
            'xsw_user_exp.id',
            '=',
            'share.target_id'
        )->select([
            'xsw_user_exp.id',
            'xsw_user_exp.user_id',
            'xsw_user_exp.goods_id',
            'xsw_user_exp.title',
            'xsw_user_exp.content',
            'xsw_user_exp.like',
            'xsw_user_exp.view',
            'xsw_user_exp.collect',
            'xsw_user_exp.created_at',
            'xsw_user_exp.income',
            'status',
            'reject_reason',
            \DB::raw('count(share.id) as share'),
        ])->where('xsw_user_exp.user_id', $user->id)
            ->where('status',[UserExp::S_COMPLETED])
            ->orderBy('created_at', 'desc')
            ->groupBy('xsw_user_exp.id')->get();
    }

    /**
     * @param User $user
     *
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function linkList(User $user)
    {
        $userExpIds = UserExp::query()
            ->where('user_id', $user->id)
            ->pluck('id');

        return UserShare::query()->orderBy('id', 'DESC')
            ->with([
            'goods' => function ($q) {
                /** @var Builder $q */
                $q->select([
                    'id',
                    'name',
                ]);
            },
            ])->select([
                'id',
                'user_id',
                'goods_id',
                'income',
                'created_at',
            ])->where('user_id', $user->id)
                ->where(function (\Illuminate\Database\Eloquent\Builder $q) use ($user, $userExpIds) {
                    $q->where('target', '<>', UserShare::T_EXP)
                        ->orWhere(function (\Illuminate\Database\Eloquent\Builder $qq) use ($user, $userExpIds) {
                            $qq->where('target', UserShare::T_EXP)
                                ->whereNotIn('target_id', $userExpIds);
                        });
                })
            ->get();
    }
    
    public function income(User $user)
    {
        $shareIncome = UserShare::query()->select([
            \DB::raw('SUM(income) as total_income'),
        ])->where('user_id', $user->id)->first();
        
        $expIncome = UserExp::query()->select([
            \DB::raw('SUM(income) as total_income'),
        ])->where('user_id', $user->id)->first();
        
        return [
            'total_income' => $shareIncome->total_income + $expIncome->total_income,
        ];
    }
    
    
    public function withdrawInfo(User $user)
    {
        return UserTransaction::query()
            ->select([
                'status',
                'amount',
                'note',
                'created_at',
            ])->where('user_id', $user->id)
            ->where('type', UserTransaction::A_WITHDRAW)
            ->orderBy('id', 'desc')
            ->get();
    }
}
