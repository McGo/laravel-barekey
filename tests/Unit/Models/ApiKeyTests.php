<?php

namespace McGo\Barekey\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use McGo\Barekey\Models\ApiKey;
use McGo\Barekey\Tests\BaseTestCase;
use PHPUnit\Framework\Attributes\Test;

class ApiKeyTests extends BaseTestCase
{
    use RefreshDatabase;
    use WithFaker;

    #[Test]
    public function it_casts_correctly()
    {
        // Given
        $token = Str::random(64);
        $key = ApiKey::factory()->create([
            'last_used_at' => $this->faker->dateTime,
            'expires_at' => $this->faker->dateTime,
            'revoked_at' => $this->faker->dateTime,
            'token' => $token,
        ]);

        // When & Then
        $this->assertIsObject($key->abilities);
        $this->assertEquals(Carbon::class, get_class($key->last_used_at));
        $this->assertEquals(Carbon::class, get_class($key->expires_at));
        $this->assertEquals(Carbon::class, get_class($key->revoked_at));

        // Check that token is same if access via cast
        $this->assertEquals($token, $key->token);
        // But is not stored in DB
        $this->assertNotEquals($token, DB::table('barekey_apikeys')->where('uuid', '=', $key->uuid)->first()->token);
    }

    #[Test]
    public function it_calculates_scope_isActive()
    {
        // For null values
        $this->assertTrue(ApiKey::factory()->create()->isActive());

        // For expired in Future
        $this->assertTrue(
            ApiKey::factory()->create(['expires_at' => Carbon::now()->addDays(10),])->isActive()
        );

        // For expired and revoked in Future
        $this->assertTrue(
            ApiKey::factory()->create([
                'expires_at' => Carbon::now()->addDays(10),
                'revoked_at' => Carbon::now()->addDays(10),
            ])->isActive()
        );

        // For revoked in Future
        $this->assertTrue(ApiKey::factory()->create(['revoked_at' => Carbon::now()->addDays(10),])->isActive());

        // Inactive if either or both is in past and the other is null or in future
        $this->assertFalse(ApiKey::factory()->create(['revoked_at' => Carbon::now()->subDays(10),])->isActive());
        $this->assertFalse(ApiKey::factory()->create(['expires_at' => Carbon::now()->subDays(10),])->isActive());
        $this->assertFalse(ApiKey::factory()->create([
            'expires_at' => Carbon::now()->subDays(10),
            'revoked_at' => Carbon::now()->subDays(10),
        ])->isActive());
        $this->assertFalse(ApiKey::factory()->create([
            'expires_at' => Carbon::now()->addDays(10),
            'revoked_at' => Carbon::now()->subDays(10),
        ])->isActive());
        $this->assertFalse(ApiKey::factory()->create([
            'expires_at' => Carbon::now()->subDays(10),
            'revoked_at' => Carbon::now()->addDays(10),
        ])->isActive());
    }

    #[Test]
    public function it_calculates_can_correctly()
    {
        // If no abilities set, all can are false
        $key = ApiKey::factory()->create([]);
        $this->assertFalse($key->can($this->faker->word));

        // If abilities are set, only those are set will return true - respect asterisks
        $key = ApiKey::factory()->create([
            'abilities' => ['test:ability', 'asterisk:abilities']
        ]);
        $this->assertFalse($key->can('not:set'));
        $this->assertTrue($key->can('test:ability'));
        $this->assertTrue($key->can('asterisk:*'));
    }
}