<?php

namespace Samrap\Gemini\Tests\Unit;

use Samrap\Gemini\Payload;
use Samrap\Gemini\Tests\TestCase;

class PayloadTest extends TestCase
{
    /** @test */
    public function it_generates_a_nonce()
    {
        $uri = '/v1/order/status';
        $data = ['order_id' => 18834];
        $payload = new Payload($uri, $data);

        $this->assertEquals(
            (string) round(microtime(true)),
            $payload->getNonce()
        );
    }
}
