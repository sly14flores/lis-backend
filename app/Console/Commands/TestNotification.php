<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Notification;
use App\Jobs\BroadcastNotificationJob;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test notification';

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

        $notification = new Notification;
        $notification->user_id = 28;
        $notification->subject = "New Notification";
        $notification->content = "Bla bla bla bla";
        $notification->save();

        BroadcastNotificationJob::dispatch(28,"New Notification")->delay(now()->addSeconds(2));        
        return 0;
    }
}
