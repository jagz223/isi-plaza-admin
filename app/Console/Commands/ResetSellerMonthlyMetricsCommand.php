<?php

namespace App\Console\Commands;

use App\Services\Seller\SellerMetricsService;
use Illuminate\Console\Command;

class ResetSellerMonthlyMetricsCommand extends Command
{
    protected $signature = 'seller:reset-monthly-metrics';

    protected $description = 'Elimina eventos de interacción de meses anteriores (reinicio mensual de métricas)';

    public function handle(SellerMetricsService $metrics): int
    {
        $deleted = $metrics->purgeEventsBeforeCurrentMonth();

        $this->info("Eventos eliminados: {$deleted}");

        return self::SUCCESS;
    }
}
