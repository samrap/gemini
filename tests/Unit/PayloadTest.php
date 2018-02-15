<?php

namespace Samrap\Gemini\Tests\Unit;

use Samrap\Gemini\Payload;
use Samrap\Gemini\Tests\TestCase;

class PayloadTest extends TestCase
{
    /** @test */
    public function it_generates_a_nonce()
    {
        $endpoint = '/v1/order/status';
        $data = ['order_id' => 18834];
        $payload = new Payload($endpoint, $data);

        $this->assertEquals(
            (string) round(microtime(true)),
            $payload->getNonce()
        );
    }

    /** @test */
    public function it_converts_to_array()
    {
        $endpoint = '/v1/order/status';
        $data = ['order_id' => 18834];
        $payload = new Payload($endpoint, $data);

        $this->assertEquals([
            'request' => $endpoint,
            'nonce' => $payload->getNonce(),
            'order_id' => $data['order_id'],
        ], $payload->toArray());
    }
}
