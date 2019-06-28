<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/13
 * Time: 17:10
 */

namespace App\Events\Transaction;

use App\Models\UserTransaction;

class UserWithdrawAudited
{
    public $transaction;

    public function __construct(UserTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
