<?php
// routes/console.php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\AccrueMonthlyLeave;
use App\Console\Commands\ProcessYearRollover;
use Illuminate\Support\Facades\Artisan;

// Ajouter 2.5 jours de congé le 1er de chaque mois à 00:01
Schedule::command(AccrueMonthlyLeave::class)
    ->monthlyOn(1, '00:01')
    ->withoutOverlapping()
    ->runInBackground()
    ->onOneServer();

// Traiter le report annuel le 1er janvier à 00:01
Schedule::command(ProcessYearRollover::class)
    ->yearlyOn(1, 1, '00:01')
    ->withoutOverlapping()
    ->runInBackground()
    ->onOneServer();

// name() est obligatoire avant onOneServer() pour les closures
Schedule::call(function () {
    $today = now();

    if ($today->day === 1) {
        Artisan::call('leave:accrue-monthly');
    }

    if ($today->month === 1 && $today->day === 1) {
        Artisan::call('leave:year-rollover');
    }
})
->dailyAt('00:01')
->name('daily-leave-check')
->onOneServer();

// Avec gestion des environnements
if (app()->environment('production')) {
    Schedule::command(AccrueMonthlyLeave::class)
        ->monthlyOn(1, '00:01')
        ->onOneServer();
}
