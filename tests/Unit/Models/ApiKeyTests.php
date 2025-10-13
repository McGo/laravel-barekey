<?php

namespace Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use McGo\Barekey\Tests\BaseTestCase;
use PHPUnit\Framework\Attributes\Test;

class ApiKeyTests extends BaseTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_casts_correctly()
    {
        $this->writeTests();
    }

    #[Test]
    public function it_calculates_scope_isActive()
    {
        $this->writeTests();
    }

    #[Test]
    public function it_calculates_can_correctly()
    {
        $this->writeTests();
    }
}