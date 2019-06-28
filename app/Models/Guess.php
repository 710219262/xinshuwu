<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 14:40
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Guess extends Model
{
    use SoftDeletes;
    
    protected $table = 'xsw_guess';
    
    protected $guarded = ['id'];
    
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    /**
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }
    /**
     * @param $phone
     *
     * @return string
     */
    public static function newOrderNum()
    {
        $phone = rand(100000000,999999999);
        $orderNo = self::generateNum($phone);
        while (Guess::query()->where('guess_no', $orderNo)->count() > 0) {
            $orderNo = self::generateNum($phone);
        }
        return $orderNo;
    }

    /**
     * @param $phone
     *
     * @return string
     */
    protected static function generateNum($phone)
    {
        return sprintf(
            "%s%s%s",
            time(),
            substr($phone, -4),
            rand(10, 99)
        );
    }
}
