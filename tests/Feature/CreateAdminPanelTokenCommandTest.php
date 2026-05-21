<?php

use App\Models\AdminToken;
use Illuminate\Support\Facades\Hash;

it('creates an admin panel token with a given plain value', function (): void {
    $this->artisan('isi-plaza:create-token', [
        'token' => 'mytoken12345',
        '--description' => 'CLI test',
    ])->assertSuccessful();

    $record = AdminToken::query()->where('description', 'CLI test')->first();

    expect($record)->not->toBeNull()
        ->and($record?->is_active)->toBeTrue()
        ->and(Hash::check('mytoken12345', (string) $record?->token_hash))->toBeTrue();
});

it('rejects tokens outside the allowed length', function (): void {
    $this->artisan('isi-plaza:create-token', [
        'token' => 'short',
    ])->assertFailed();

    expect(AdminToken::query()->count())->toBe(0);
});
