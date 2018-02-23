<?php

namespace Samrap\Gemini\Tests\Unit;

use Samrap\Gemini\Payload;
use Samrap\Gemini\Signature;
use Samrap\Gemini\Tests\TestCase;

class SignatureTest extends TestCase
{
    /** @test */
    public function it_generates_a_valid_signature()
    {
        $api_secret = '1234abcd';
        $endpoint = '/v1/order/status';
        $data = ['order_id' => 18834];
        $payload = new Payload($endpoint, $data);

        $this->assertEquals(
            hash_hmac('sha384', $payload->encode(), $api_secret),
            Signature::generate($payload, $api_secret)
        );
    }
}
