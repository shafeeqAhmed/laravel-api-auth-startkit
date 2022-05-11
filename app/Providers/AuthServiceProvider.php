<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            $front_end_url = config('app.front_end_url');

            $url = str_replace(url('/'), $front_end_url, $url);

            return (new MailMessage)
                ->subject('Verify Email Address updated')
                ->line('Click the button below to verify your email address.updated')
                ->action('Verify Email Address updated', $url);
        });
        ResetPassword::createUrlUsing(function ($user, string $token) {
            $url = config('app.front_end_url');
            return $url . '/account/reset?token=' . $token;
        });
    }
}
