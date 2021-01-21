<?php

namespace Cloudstacks\Gemini\Tests\Unit;

use Cloudstacks\Gemini\Payload;
use Cloudstacks\Gemini\Signature;
use Cloudstacks\Gemini\Tests\TestCase;

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
