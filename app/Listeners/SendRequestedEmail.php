<?php

namespace App\Listeners;

use App\Events\UserRequestedPassword;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
class SendRequestedEmail implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRequestedPassword $event): void
    {
        // \Log::info('Listener Request triggered!');
        Mail::to($event->user->email)->queue(new ResetPasswordMail($event->resetURL));
    }
}
