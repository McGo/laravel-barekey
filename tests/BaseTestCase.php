<?php

namespace McGo\Barekey\Tests;

use McGo\Barekey\BarekeyServiceProvider;
use Orchestra\Testbench\TestCase;

class BaseTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            BarekeyServiceProvider::class,
        ];
    }

    protected function writeTests()
    {
        $this->assertTrue(false, 'Test has to be written!');
    }
}