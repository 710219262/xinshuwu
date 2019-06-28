<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 22/03/2019
 * Time: 12:29
 */

namespace App\Http\Controllers\MMS\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserTransaction;
use App\Models\MerchantTransaction;
use App\Models\PlatformTransaction;
use App\Repos\Admin\TransactionRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Transaction extends Controller
{
    public function userlist(Request $request,TransactionRepo $transactionRepo)
    {
        $this->validate($request, [
            'query'               => 'array'
        ]);

        if(!empty($request->input('ispage'))) {
            $pageSize = empty($request->input('pageSize')) ? 10 : $request->input('pageSize');
            $pageIndex = empty($request->input('pageIndex')) ? 1 : $request->input('pageIndex');
            $offset = ceil($pageSize * ($pageIndex - 1));
            $exps = $transactionRepo->userlist($request->input('query'), $offset, $pageSize);
        }else{
            $exps = $transactionRepo->userlist($request->input('query'));
        }
        return json_response($exps);
    }

}
