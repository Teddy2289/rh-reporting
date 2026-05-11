<?php

namespace App\Console\Commands;

use App\Services\LeaveService;
use Illuminate\Console\Command;

class AccrueMonthlyLeave extends Command
{
    protected $signature = 'leave:accrue-monthly';
    protected $description = 'Ajoute 2.5 jours de congé à chaque agent actif au début du mois';

    public function handle(LeaveService $leaveService)
    {
        $this->info('Ajout des jours de congé mensuels...');
        $leaveService->addMonthlyLeaveDays();
        $this->info('Ajout terminé avec succès.');
    }
}
