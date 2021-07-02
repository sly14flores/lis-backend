<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\TestJob;

class TestEventBroadcastDispatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-broadcast:dispatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test broadcast';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
		TestJob::dispatch(["message"=>"Lorem, Ipsum"])->delay(now()->addSeconds(5));       
        return 0;
    }
}
