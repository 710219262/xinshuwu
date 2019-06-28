<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/03/2019
 * Time: 22:34
 */

namespace App\Http\Controllers\Api\User;

use App\Events\Exp\ExpWasCollected;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCollection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Collection extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'type' => [
                'required',
                Rule::in([
                    UserCollection::T_ARTICLE,
                    UserCollection::T_EXP,
                    UserCollection::T_GOODS,
                    UserCollection::T_STORE,
                ]),
            ],
        ]);
        
        $type = $request->input('type');
        $table = UserCollection::T_TABLE_MAP[$type];
        
        $this->validate($request, [
            'collect_id' => ['required', 'int', Rule::exists($table, 'id')],
        ]);
        
        
        /** @var User $user */
        $user = $request->user();
        $collectId = $request->input('collect_id');
        
        /** @var UserCollection $collect */
        $collect = UserCollection::query()->firstOrCreate([
            'type'       => $type,
            'collect_id' => $collectId,
            'user_id'    => $user->id,
        ]);
        
        //todo fix repeat count collect
        \DB::table($table)
            ->where('id', $collectId)
            ->increment('collect');
        
        $request->input('type') === UserCollection::T_EXP && event(new ExpWasCollected($collect));
        
        return json_response();
    }
    
    /**
     * 用户收藏列表
     *
     * @param Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|array',
        ]);
        
        $type = array_unique($request->input('type'));
        $list = [];
        foreach ($type as $t ) {
            $m = strtolower($t) . 'Collections';
            if (method_exists($request->user(), $m)) {
                $list = array_merge($request->user()->{$m}(), $list);
            }
        }
        $sortList = collect($list)->sortBy(function ($item) {
            return $item['id'];
        }, SORT_NUMERIC, true)->values()->all();

        return json_response($sortList);
    }
    
    /**
     * 取消收藏
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delete(Request $request)
    {
        
        $this->validate($request, [
            'type' => [
                'required',
                Rule::in([
                    UserCollection::T_ARTICLE,
                    UserCollection::T_EXP,
                    UserCollection::T_GOODS,
                    UserCollection::T_STORE,
                ]),
            ],
        ]);
        
        $ids = is_array($request->input('collect_id')) ?
            $request->input('collect_id') :
            [$request->input('collect_id')];
        
        $type = $request->input('type');
        
        $table = UserCollection::T_TABLE_MAP[$type];
        $uid = $request->user()->id;
        
        $this->validate($request, [
            'collect_id' => 'required',
            Rule::exists('xsw_user_collection')->whereIn('collect_id', $ids)
                ->where('type', $type)
                ->where('user_id', $uid),
        ]);
        
        $count = UserCollection::query()
            ->whereIn('collect_id', $ids)
            ->where('type', $type)
            ->where('user_id', $uid)
            ->delete();
        
        if ($count > 0) {
            \DB::table($table)
                ->whereIn('id', $ids)
                ->decrement('collect');
        }
        
        return json_response();
    }
}
