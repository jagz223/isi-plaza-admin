<?php

namespace App\Console\Commands;

use App\Models\AdminToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminPanelTokenCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'isi-plaza:create-token
                            {token? : Plain token (9–15 characters). Random if omitted.}
                            {--description= : Optional label stored in admin_tokens.description}';

    /**
     * @var string
     */
    protected $description = 'Create an ISI PLAZA admin panel access token (shown once in the console)';

    public function handle(): int
    {
        $plain = $this->argument('token') ?? Str::random(random_int(9, 15));

        if (strlen($plain) < 9 || strlen($plain) > 15) {
            $this->error('The token must be between 9 and 15 characters.');

            return self::FAILURE;
        }

        AdminToken::query()->create([
            'token_hash' => Hash::make($plain),
            'description' => $this->option('description'),
            'is_active' => true,
        ]);

        $this->newLine();
        $this->info('Admin panel token created successfully.');
        $this->line('Copy this token now — it will not be shown again:');
        $this->newLine();
        $this->line("  <fg=bright-white;options=bold>{$plain}</>");
        $this->newLine();
        $this->comment('Sign in at /isi-plaza/access or use it as Bearer / X-Admin-Token for the admin API.');

        return self::SUCCESS;
    }
}
