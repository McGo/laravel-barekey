<?php

namespace McGo\Barekey\Commands;

use Illuminate\Console\Command;
use McGo\Barekey\Models\ApiKey;


class MakeApiKey extends Command
{
    protected $signature = 'barekey:make {name} {--abilities=}';
    protected $description = 'Create a new API key.';

    public function handle(): int
    {
        $name = $this->argument('name');

        $abilities = $this->option('abilities')
            ? array_map('trim', explode(',', $this->option('abilities')))
            : null;

        $key = ApiKey::create([
            'name' => $name,
            'abilities' => $abilities
        ]);

        $this->info('API Key generated, please use it as the following header:');
        $this->line("Authorization: Bearer {$key->uuid}:{$key->token}");
        $this->line("Or as custom header:");
        $this->line("X-Barekey-Token: {$key->uuid}:{$key->token}");
        return self::SUCCESS;
    }

}
