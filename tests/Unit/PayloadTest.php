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

        $this->assertEquals(round(microtime(true)), $payload->getNonce());
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

    /** @test */
    public function it_converts_to_json()
    {
        $endpoint = '/v1/order/status';
        $data = ['order_id' => 18834];
        $payload = new Payload($endpoint, $data);

        $this->assertEquals(
            '{"request":"/v1/order/status","nonce":'.$payload->getNonce().',"order_id":18834}',
            $payload->toJson()
        );
    }

    /** @test */
    public function it_base64_encodes_the_payload()
    {
        $endpoint = '/v1/order/status';
        $data = ['order_id' => 18834];
        $payload = new Payload($endpoint, $data);

        $this->assertEquals(
            base64_encode($payload->toJson()),
            $payload->encode()
        );
    }
}
