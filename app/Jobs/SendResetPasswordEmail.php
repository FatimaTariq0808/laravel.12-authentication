<?php

namespace App\Jobs;

use App\Mail\ResetPasswordMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class SendResetPasswordEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $user;
    public $resetURL;

    public function __construct(User $user, string $resetURL)
    {
        $this->user = $user;
        $this->resetURL = $resetURL;
    }

    public function handle(): void
    {
        Mail::to($this->user->email)->queue(new ResetPasswordMail($this->resetURL));
    }
}
