<?php

namespace App\Console\Commands;

use App\Services\Firestore\FirestoreRestClient;
use App\Services\Platform\PlatformAccessService;
use Illuminate\Console\Command;
use Throwable;

class PlatformAccessStatusCommand extends Command
{
    protected $signature = 'platform:status';

    protected $description = 'Muestra el valor de app_enabled en Firestore y si el middleware dejaría pasar tráfico';

    public function handle(FirestoreRestClient $firestore, PlatformAccessService $platformAccess): int
    {
        $config = config('platform.firestore');

        try {
            $raw = $firestore->getBooleanField(
                $config['collection'],
                $config['document'],
                $config['field'],
            );
        } catch (Throwable $e) {
            $this->error('Error leyendo Firestore: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($raw === null) {
            $this->warn('Documento no encontrado o campo ausente → fail_open='.(config('platform.fail_open') ? 'true (pasa)' : 'false (bloquea)'));
        } else {
            $this->info('Firestore app_enabled = '.($raw ? 'true' : 'false'));
        }

        $enabled = $platformAccess->isAppEnabled();
        $this->line('Middleware permitiría acceso: '.($enabled ? 'SÍ' : 'NO (pantalla negra / 503)'));

        return self::SUCCESS;
    }
}
