<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector;

/**
 * Solo para libpq < 14 (p. ej. XAMPP). Con PHP/Scoop moderno no hace falta.
 *
 * @see https://render.com/docs/postgresql-creating-connecting
 */
class RenderPostgresConnector extends PostgresConnector
{
    public function connect(array $config)
    {
        if ($this->usesLegacyEndpoint($config) && ! empty($config['password'])) {
            $config['password'] = 'endpoint='.$config['pg_endpoint'].';'.$config['password'];
        }

        return parent::connect($config);
    }

    protected function addSslOptions($dsn, array $config)
    {
        $dsn = parent::addSslOptions($dsn, $config);

        if ($this->usesLegacyEndpoint($config)) {
            $dsn .= ';options=endpoint%3D'.$config['pg_endpoint'];
        }

        return $dsn;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function usesLegacyEndpoint(array $config): bool
    {
        return filter_var(env('DB_PG_LEGACY_ENDPOINT', false), FILTER_VALIDATE_BOOL)
            && ! empty($config['pg_endpoint']);
    }
}
