<?php

namespace Samrap\Gemini\Tests\Unit;

use Http\Mock\Client;
use Samrap\Gemini\Gemini;
use Samrap\Gemini\Tests\TestCase;

class GeminiTest extends TestCase
{
    /** @test */
    public function it_sends_a_public_request()
    {
        $key = 'mykey';
        $secret = '1234abcd';
        $client = new Client();
        $gemini = new Gemini($key, $secret, $client);

        $gemini->publicRequest('symbols');

        $this->assertEquals(
            'https://api.gemini.com/v1/symbols',
            (string) $client->getRequests()[0]->getUri()
        );
        $this->assertEquals('GET', $client->getRequests()[0]->getMethod());
    }

    /** @test */
    public function it_sends_a_private_request()
    {
        $key = 'mykey';
        $secret = '1234abcd';
        $client = new Client();
        $gemini = new Gemini($key, $secret, $client);

        $gemini->privateRequest('order/new');

        $this->assertEquals(
            'https://api.gemini.com/v1/order/new',
            (string) $client->getRequests()[0]->getUri()
        );
        $this->assertEquals('POST', $client->getRequests()[0]->getMethod());
    }

    /** @test */
    public function a_private_request_sets_the_proper_headers()
    {
        $key = 'mykey';
        $secret = '1234abcd';
        $client = new Client();
        $gemini = new Gemini($key, $secret, $client);

        $gemini->privateRequest('order/new', [
            'symbol' => 'ethusd',
            'amount' => '100.00',
            'side' => 'buy',
        ]);

        $request = $client->getRequests()[0];

        $this->assertTrue($request->hasHeader('X-GEMINI-PAYLOAD'));
        $this->assertTrue($request->hasHeader('X-GEMINI-SIGNATURE'));
        $this->assertEquals(['text/plain'], $request->getHeader('Content-Type'));
        $this->assertEquals(['0'], $request->getHeader('Content-Length'));
        $this->assertEquals([$key], $request->getHeader('X-GEMINI-APIKEY'));
        $this->assertEquals('no-cache', $request->getHeader('Cache-Control'));
    }
}
