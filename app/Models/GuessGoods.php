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

class GuessGoods extends Model
{
    use SoftDeletes;
    
    protected $table = 'xsw_guess_goods';
    
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

    public function stage()
    {
        return $this->belongsTo(
            GuessStage::class,
            'stage_id',
            'id'
        );
    }
}
