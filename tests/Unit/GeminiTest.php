<?php

namespace Samrap\Gemini\Tests\Unit;

use Http\Client\Exception\TransferException;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Mock\Client;
use Samrap\Gemini\Gemini;
use Samrap\Gemini\Tests\TestCase;

class GeminiTest extends TestCase
{
    /** @var \Http\Message\MessageFactory */
    private $messageFactory;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->messageFactory = new GuzzleMessageFactory();
    }

    /** @test */
    public function it_sends_a_public_request()
    {
        $client = new Client();
        $gemini = new Gemini('', '', $client);

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
        $this->assertEquals(['no-cache'], $request->getHeader('Cache-Control'));
    }

    /** @test */
    public function a_public_request_returns_just_the_json_data()
    {
        $client = new Client();
        $response = $this->messageFactory->createResponse(
            200,
            null,
            [],
            json_encode(['btcusd', 'ethbtc', 'ethusd'])
        );
        $client->addResponse($response);
        $gemini = new Gemini('', '', $client);

        $result = $gemini->publicRequest('symbols');

        $this->assertEquals(['btcusd', 'ethbtc', 'ethusd'], $result);
    }

    /** @test */
    public function a_private_request_returns_just_the_json_data()
    {
        $key = 'mykey';
        $secret = '1234abcd';
        $client = new Client();
        $response = $this->messageFactory->createResponse(
            200,
            null,
            [],
            json_encode(['order_id' => '22333'])
        );
        $client->addResponse($response);
        $gemini = new Gemini($key, $secret, $client);

        $result = $gemini->privateRequest('order/new', [
            'symbol' => 'ethusd',
            'amount' => '100.00',
            'side' => 'buy',
        ]);

        $this->assertEquals(['order_id' => '22333'], $result);
    }

    /**
     * @test
     * @expectedException \Samrap\Gemini\Exceptions\ClientException
     * @expectedExceptionMessage An error occurred while talking to the API.
     */
    public function it_catches_an_http_transfer_exception_and_throws_a_client_exception()
    {
        $key = 'mykey';
        $secret = '1234abcd';
        $client = new Client();
        $client->addException(new TransferException());
        $gemini = new Gemini($key, $secret, $client);

        $gemini->publicRequest('symbols');
    }

    /**
     * @test
     * @expectedException \Samrap\Gemini\Exceptions\AuctionNotOpenException
     * @expectedExceptionMessage Failed to place an auction-only order because there is no current auction open for this symbol
     */
    public function it_throws_the_respective_exception_from_an_error_response()
    {
        $this->sendFailedRequest(400, [
            'result' => 'error',
            'reason' => 'AuctionNotOpen',
            'message' => 'Failed to place an auction-only order because there is no current auction open for this symbol',
        ]);
    }

    /**
     * @test
     * @expectedException \Samrap\Gemini\Exceptions\GeminiException
     * @expectedExceptionMessage [Bad Request] Supplied parameter is not a valid option
     */
    public function unknown_exception_contains_reason_and_message()
    {
        $this->sendFailedRequest(400, [
            'result' => 'error',
            'reason' => 'Bad Request',
            'message' => 'Supplied parameter is not a valid option',
        ]);
    }

    /**
     * Send a request that should.
     *
     * @param  int  $statusCode  The expected response status code.
     * @param  array  $errorPayload  The expected error payload.
     * @throws \Samrap\Gemini\Excpetions\GeminiException
     * @return void
     */
    private function sendFailedRequest(int $statusCode, array $errorPayload)
    {
        $key = 'mykey';
        $secret = '1234abcd';
        $client = new Client();
        $response = $this->messageFactory->createResponse(
            $statusCode,
            null,
            [],
            json_encode($errorPayload)
        );
        $client->addResponse($response);
        $gemini = new Gemini($key, $secret, $client);

        $gemini->publicRequest('dummy');
    }
}
