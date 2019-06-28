<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 29/03/2019
 * Time: 17:49
 */

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CancelTimeoutOrder extends Command
{
    const TIMEOUT_IN_MIN = 60;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stale_order:cancel';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cancel stale order';
    
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
        $builder = Order::query()
            ->where('status', '=', Order::S_CREATED)
            ->where(
                'created_at',
                '<',
                Carbon::now()->subMinute(self::TIMEOUT_IN_MIN)
            );
        
        //todo notify user order  was canceled
        $staleOrderIds = (clone $builder)->pluck('id')->toArray();
        
        $builder->update([
            'status' => Order::S_CANCELED,
        ]);
    }
}
