<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\PlanningSlot;
use App\Enums\SlotType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class PlanningGeneratorService
{
    /**
     * Jours fériés de Madagascar (à adapter selon l'année).
     */
    private function getMadagascarHolidays(int $year): array
    {
        return [
            "{$year}-01-01", // Nouvel An
            "{$year}-03-29", // Journée des Martyrs
            "{$year}-04-18", // Vendredi Saint (approximatif)
            "{$year}-04-21", // Lundi de Pâques
            "{$year}-05-01", // Fête du Travail
            "{$year}-05-25", // Journée de l'OUA / Afrique
            "{$year}-05-29", // Ascension
            "{$year}-06-09", // Lundi de Pentecôte
            "{$year}-06-26", // Fête Nationale
            "{$year}-08-15", // Assomption
            "{$year}-11-01", // Toussaint
            "{$year}-12-25", // Noël
        ];
    }

    /**
     * Génère le planning annuel pour un agent donné.
     * Slots par défaut : 09:00–13:00 et 14:00–17:30, pause 13:00–14:00
     */
    public function generateForAgent(Agent $agent, int $year, bool $overwrite = false): int
    {
        $holidays = $this->getMadagascarHolidays($year);
        $from     = Carbon::create($year, 1, 1);
        $to       = Carbon::create($year, 12, 31);
        $period   = CarbonPeriod::create($from, $to);
        $created  = 0;

        DB::transaction(function () use ($agent, $period, $holidays, $overwrite, &$created) {
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');

                // Skip week-ends
                if ($date->isWeekend()) continue;

                // Skip si déjà existant et pas d'écrasement
                if (!$overwrite) {
                    $exists = PlanningSlot::where('agent_id', $agent->id)
                        ->where('date', $dateStr)
                        ->exists();
                    if ($exists) continue;
                } else {
                    PlanningSlot::where('agent_id', $agent->id)
                        ->where('date', $dateStr)
                        ->delete();
                }

                // Jour férié
                if (in_array($dateStr, $holidays)) {
                    PlanningSlot::create([
                        'agent_id'   => $agent->id,
                        'date'       => $dateStr,
                        'time_start' => '09:00',
                        'time_end'   => '17:30',
                        'type'       => SlotType::Holiday->value,
                        'note'       => 'Jour férié',
                        'created_by' => 1, // system
                    ]);
                    $created++;
                    continue;
                }

                // Matin 09:00 → 13:00 (4h)
                PlanningSlot::create([
                    'agent_id'   => $agent->id,
                    'date'       => $dateStr,
                    'time_start' => '09:00',
                    'time_end'   => '13:00',
                    'type'       => SlotType::Work->value,
                    'created_by' => 1,
                ]);

                // Pause 13:00 → 14:00
                PlanningSlot::create([
                    'agent_id'   => $agent->id,
                    'date'       => $dateStr,
                    'time_start' => '13:00',
                    'time_end'   => '14:00',
                    'type'       => SlotType::Pause->value,
                    'created_by' => 1,
                ]);

                // Après-midi 14:00 → 17:30 (3h30)
                PlanningSlot::create([
                    'agent_id'   => $agent->id,
                    'date'       => $dateStr,
                    'time_start' => '14:00',
                    'time_end'   => '17:30',
                    'type'       => SlotType::Work->value,
                    'created_by' => 1,
                ]);

                $created++;
            }
        });

        return $created;
    }

    /**
     * Génère le planning annuel pour tous les agents actifs.
     */
    public function generateForAll(int $year, bool $overwrite = false): array
    {
        $agents  = Agent::active()->get();
        $results = [];

        foreach ($agents as $agent) {
            $count = $this->generateForAgent($agent, $year, $overwrite);
            $results[] = [
                'agent'   => $agent->full_name,
                'created' => $count,
            ];
        }

        return $results;
    }
}
