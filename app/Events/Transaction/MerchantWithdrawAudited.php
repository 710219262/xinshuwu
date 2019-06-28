<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/10
 * Time: 15:19
 */
namespace App\Events\Transaction;

use \App\Models\MerchantTransaction;

class MerchantWithdrawAudited
{
    public $transaction;

    public function __construct(MerchantTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
