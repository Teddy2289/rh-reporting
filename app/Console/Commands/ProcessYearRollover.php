<?php

namespace App\Console\Commands;

use App\Services\LeaveService;
use Illuminate\Console\Command;

class ProcessYearRollover extends Command
{
    protected $signature = 'leave:year-rollover';
    protected $description = 'Traite le report des congés en début d\'année';

    public function handle(LeaveService $leaveService)
    {
        $this->info('Traitement du report annuel des congés...');
        $leaveService->processNewYearRollover();
        $this->info('Report terminé avec succès.');
    }
}
