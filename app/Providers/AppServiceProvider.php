<?php

namespace App\Providers;

use App\Models\Owner;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
        //     return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        // });
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            $userType = $notifiable instanceof Owner ? 'owners' : 'users'; // تأكد من أن لديك كلاس Owner
    
            return config('app.frontend_url') . "/password-reset/$userType/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
