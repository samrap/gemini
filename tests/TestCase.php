<?php

namespace Samrap\Gemini\Tests;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        HttpClientDiscovery::appendStrategy(MockClientStrategy::class);
    }
}
