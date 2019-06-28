<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 18/03/2019
 * Time: 22:09
 */

namespace App\Repos\User;

use App\Models\User;

class UserRepo
{
    /**
     * Get user by varies id
     * eg: wechat_uid,qq_uid,wb_uid,phone....
     *
     * @param $by string
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function getUserById($by, $id)
    {
        $key = User::L_ID_MAP[$by];
        return User::query()->where($key, $id)
            ->first();
    }
    
    /**
     * 查询用户或者注册新用户
     *
     * @param        $by
     * @param        $id
     * @param        $data
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|User
     */
    public function getUserOrNew($by, $id, $data)
    {
        return User::query()->firstOrCreate([
            User::L_ID_MAP[$by] => $id,
        ], $data);
    }

    /**
     *
     *
     * @param        $by
     * @param        $id
     * @param        $data
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|User
     */
    public function bindUserOrNew($by, $id, $data)
    {
        $user = User::query()->where('phone', $data['phone'])
            ->first();
        if(empty($user)){
            return User::query()->firstOrCreate([
                User::L_ID_MAP[$by] => $id,
            ], $data);
        }else {
            $u = array(User::L_ID_MAP[$by] => $id);
            if(empty($user['avatar']) || ($user['avatar'] == 'avatar.png')) $u['avatar'] = $data['avatar'];
            if(empty($user['nickname'])) $u['nickname'] = $data['nickname'];
            if(empty($user['gender'])) $u['gender'] = $data['gender'];
            $user->update($u);
//            $user->update([
//                User::L_ID_MAP[$by] => $id
//            ]);
            return $user;
        }
    }
    /**
     *
     *
     * @param        $by
     * @param        $id
     * @param        $data
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|User
     */
    public function bindUserStatus($by, $id, $data)
    {
        $user = User::query()->where('phone', $data['phone'])
            ->first();
        if(!empty($user) && !empty($user[User::L_ID_MAP[$by]])){
            $user = $user->toArray();
            return $user['id'];
        }else {
            return 0;
        }
    }
}
