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

// Alternative : Exécution quotidienne avec condition (pour plus de flexibilité)
Schedule::call(function () {
    $today = now();

    // Vérifier si c'est le 1er du mois
    if ($today->day === 1) {
        Artisan::call('leave:accrue-monthly');
    }

    // Vérifier si c'est le 1er janvier
    if ($today->month === 1 && $today->day === 1) {
        Artisan::call('leave:year-rollover');
    }
})->dailyAt('00:01')->onOneServer();

// Avec gestion des environnements
if (app()->environment('production')) {
    Schedule::command(AccrueMonthlyLeave::class)
        ->monthlyOn(1, '00:01')
        ->onOneServer();
}
