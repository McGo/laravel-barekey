<?php

namespace McGo\Barekey\Tests\Unit\Observers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use McGo\Barekey\Models\ApiKey;
use McGo\Barekey\Tests\BaseTestCase;
use PHPUnit\Framework\Attributes\Test;

class CreateApiKeyCalcualtedFieldsTests extends BaseTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_fieldvalues()
    {
        // Given & When
        $key = ApiKey::factory()->create();

        // Then
        $this->assertNotEmpty($key->uuid);
        $this->assertNotEmpty($key->token);
    }
}