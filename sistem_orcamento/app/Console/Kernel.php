<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\CheckExpiredSubscriptions::class,
        Commands\ActivateSubscription::class,
        Commands\CancelSubscription::class,
        Commands\CheckPayments::class,
        Commands\CheckSubscription::class,
        Commands\CheckSubscriptions::class,
        Commands\CreateManualSubscription::class,
        Commands\FixPlanUpgradeData::class,
        Commands\ListCompaniesAndPlans::class,
        Commands\ListUsers::class,
        Commands\ProcessWebhookManually::class,
        Commands\TestPaymentRedirect::class,
        Commands\TestPlanUpgradeScenario::class,
        Commands\TestRealPlanUpgrade::class,
        Commands\TestWebhookPayment::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Verificar assinaturas vencidas diariamente às 9h
        $schedule->command('subscriptions:check-expired')
                 ->dailyAt('09:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/expired-subscriptions.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}