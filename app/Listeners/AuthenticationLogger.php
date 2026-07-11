<?php

namespace App\Listeners;

use App\Models\AuthenticationLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;

class AuthenticationLogger
{
    /**
     * Create the event listener.
     */
    public function __construct(protected Request $request)
    {
    }

    /**
     * Handle user login events.
     */
    public function handleUserLogin(Login $event): void
    {
        AuthenticationLog::create([
            'user_id' => $event->user->id,
            'event' => 'login',
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'email' => $event->user->email,
            'created_at' => now(),
        ]);
    }

    /**
     * Handle user logout events.
     */
    public function handleUserLogout(Logout $event): void
    {
        if ($event->user) {
            AuthenticationLog::create([
                'user_id' => $event->user->id,
                'event' => 'logout',
                'ip_address' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'email' => $event->user->email,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Handle user login failure events.
     */
    public function handleUserFailed(Failed $event): void
    {
        AuthenticationLog::create([
            'user_id' => $event->user ? $event->user->id : null,
            'event' => 'failed',
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'email' => $event->credentials['email'] ?? null,
            'created_at' => now(),
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<class-string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleUserLogin',
            Logout::class => 'handleUserLogout',
            Failed::class => 'handleUserFailed',
        ];
    }
}
