<?php

namespace McGo\Barekey\Observers;

use Illuminate\Support\Str;
use McGo\Barekey\Models\ApiKey;

class CreateApiKeyCalcualtedFields
{
    public function saving(ApiKey $apiKey)
    {
        $apiKey->uuid = $this->generateUniqueUUID();
        $apiKey->token = Str::random(64);
    }

    private function generateUniqueUUID()
    {
        while (true) {
            $uuid = Str::uuid()->toString();
            $existing = ApiKey::where('uuid', $uuid)->first();
            if (is_null($existing)) {
                return $uuid;
            }
        }
    }
}