<?php

namespace App\Services\Firestore;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Lectura de Firestore vía REST (no requiere ext-grpc en PHP).
 */
class FirestoreRestClient
{
    private const SCOPE = 'https://www.googleapis.com/auth/datastore';

    public function getDocumentFields(string $collection, string $document): ?array
    {
        $credentialsPath = $this->credentialsPath();
        $projectId = $this->projectId($credentialsPath);

        $token = $this->accessToken($credentialsPath);

        $url = sprintf(
            'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents/%s/%s',
            $projectId,
            $collection,
            $document,
        );

        $response = Http::withToken($token)
            ->acceptJson()
            ->get($url);

        if ($response->status() === 404) {
            return null;
        }

        if (! $response->successful()) {
            throw new RuntimeException(
                'Firestore REST error '.$response->status().': '.$response->body(),
            );
        }

        /** @var array{fields?: array<string, mixed>} $data */
        $data = $response->json();

        return $data['fields'] ?? null;
    }

    public function getBooleanField(string $collection, string $document, string $field): ?bool
    {
        $fields = $this->getDocumentFields($collection, $document);

        if ($fields === null || ! isset($fields[$field])) {
            return null;
        }

        $value = $fields[$field];

        if (! is_array($value) || ! array_key_exists('booleanValue', $value)) {
            Log::warning('Firestore field is not boolean', [
                'collection' => $collection,
                'document' => $document,
                'field' => $field,
            ]);

            return null;
        }

        return (bool) $value['booleanValue'];
    }

    private function credentialsPath(): string
    {
        $relative = config('firebase.projects.app.credentials')
            ?? env('FIREBASE_CREDENTIALS');

        if (! is_string($relative) || $relative === '') {
            throw new RuntimeException('FIREBASE_CREDENTIALS no está configurado.');
        }

        $path = str_starts_with($relative, DIRECTORY_SEPARATOR)
            || (strlen($relative) > 1 && $relative[1] === ':')
            ? $relative
            : base_path($relative);

        if (! is_file($path)) {
            throw new RuntimeException('No se encontró el archivo de credenciales Firebase: '.$path);
        }

        return $path;
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceAccount(string $path): array
    {
        $json = file_get_contents($path);

        if ($json === false) {
            throw new RuntimeException('No se pudo leer FIREBASE_CREDENTIALS.');
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        return $data;
    }

    private function projectId(string $credentialsPath): string
    {
        $account = $this->serviceAccount($credentialsPath);
        $projectId = $account['project_id'] ?? env('FIREBASE_PROJECT_ID');

        if (! is_string($projectId) || $projectId === '') {
            throw new RuntimeException('project_id no definido en credenciales Firebase.');
        }

        return $projectId;
    }

    private function accessToken(string $credentialsPath): string
    {
        $credentials = new ServiceAccountCredentials(
            self::SCOPE,
            $this->serviceAccount($credentialsPath),
        );

        $token = $credentials->fetchAuthToken();

        if (! isset($token['access_token']) || ! is_string($token['access_token'])) {
            throw new RuntimeException('No se pudo obtener access_token de Google.');
        }

        return $token['access_token'];
    }
}
