<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 29/03/2019
 * Time: 17:49
 */

namespace App\Console\Commands;

use App\Models\ArticleCmt;
use App\Models\ArticleCmtLike;
use App\Models\User;
use App\Models\UserCollection;
use App\Models\UserExp;
use App\Models\UserExpCmt;
use App\Models\UserExpCmtLike;
use App\Models\UserExpLike;
use App\Models\UserFollow;
use Illuminate\Console\Command;

class Stat extends Command
{
    const TIMEOUT_IN_MIN = 60;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stat';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'stat all redundant info';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    
    public function handle()
    {
        $this->updateUserInfo();
    }
    
    public function updateUserInfo()
    {
        $users = User::all();
        
        foreach ($users as $user) {
            $user->update([
                'fans_count'     => $this->calcUserFansCount($user->id),
                'follow_count'   => $this->calcUserFollowCount($user->id),
                'favorite_count' => $this->calcUserFavoriteCount($user->id),
                'liked_count'    => $this->calcUserLikedCount($user->id),
            ]);
        }
    }
    
    /**
     * @param $userId
     *
     * @return int
     */
    protected function calcUserFansCount($userId)
    {
        return UserFollow::query()
            ->where('followed_id', $userId)
            ->count();
    }
    
    /**
     * @param $userId
     *
     * @return int
     */
    protected function calcUserFollowCount($userId)
    {
        $followCount = UserFollow::query()
            ->where('follower_id', $userId)
            ->count();
        
        $storeCount = UserCollection::query()
            ->where('user_id', $userId)
            ->where('type', UserCollection::T_STORE)
            ->count();
        
        return $followCount + $storeCount;
    }
    
    /**
     * @param $userId
     *
     * @return int
     */
    protected function calcUserFavoriteCount($userId)
    {
        return UserCollection::query()
            ->where('user_id', $userId)
            ->where('type', '!=', UserCollection::T_STORE)
            ->count();
    }
    
    /**
     * @param $userId
     *
     * @return int
     */
    protected function calcUserLikedCount($userId)
    {
        $expIds = UserExp::query()
            ->where('user_id', $userId)
            ->pluck('id')
            ->toArray();
        
        $expLikeCount = UserExpLike::query()
            ->whereIn('exp_id', $expIds)
            ->count();
        
        $expCmtIds = UserExpCmt::query()
            ->where('user_id', $userId)
            ->pluck('id')
            ->toArray();
        
        $expCmtLikeCount = UserExpCmtLike::query()
            ->whereIn('comment_id', $expCmtIds)
            ->count();
        
        $articleCmtIds = ArticleCmt::query()
            ->where('user_id', $userId)
            ->pluck('id')
            ->toArray();
        
        
        $articleCmtCount = ArticleCmtLike::query()
            ->whereIn('id', $articleCmtIds)
            ->count();
        
        return $expLikeCount + $expCmtLikeCount + $articleCmtCount;
    }
}
