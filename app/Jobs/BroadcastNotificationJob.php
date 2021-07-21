<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Events\BroadcastNotification;

class BroadcastNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user_id;
    private $payload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$payload)
    {
        $this->user_id = $user_id;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        event(new BroadcastNotification($this->user_id,$this->payload));
    }
}
