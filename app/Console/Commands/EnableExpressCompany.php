<?php


namespace App\Console\Commands;

use App\Models\ExpressCompany;
use Illuminate\Console\Command;

class EnableExpressCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'express_company:enable';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'enable specific express company';
    
    public function handle()
    {
        ExpressCompany::query()
            ->whereIn('name', config('xsw.express_company'))
            ->update(['status' => ExpressCompany::ENABLE]);
    }
}
