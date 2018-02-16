<?php

namespace Samrap\Gemini;

use GuzzleHttp\Psr7\Response;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;

class Gemini
{
    /** @var string */
    const BASE_URI = 'https://api.gemini.com/v1/';

    /**
     * The API key.
     *
     * @var string
     */
    protected $key;

    /**
     * The API secret.
     *
     * @var string
     */
    protected $secret;

    /**
     * The HTTP Client implementation
     *
     * @var \Http\Client\HttpClient
     */
    protected $client;

    /**
     * The HTTP Message Factory.
     *
     * @var \Http\Message\MessageFactory
     */
    protected $messageFactory;

    /**
     * Create a new instance.
     *
     * @param  string  $key
     * @param  string  $secret
     * @param  \Http\Client\HttpClient|null  $client
     * @param  \Http\Message\MessageFactory|null  $messageFactory
     */
    public function __construct(string $key, string $secret, HttpClient $client = null, MessageFactory $messageFactory = null)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->client = $client ?: HttpClientDiscovery::find();
        $this->messageFactory = $messageFactory ?: MessageFactoryDiscovery::find();
    }

    /**
     * Send a public request to the given API and retrieve the response.
     *
     * @param  string  $api
     * @return \GuzzleHttp\Psr7\Response
     */
    public function publicRequest(string $api) : Response
    {
        $uri = self::BASE_URI.trim($api, '/');
        $request = $this->messageFactory->createRequest('GET', $uri);

        return $this->client->sendRequest($request);
    }

    /**
     * [privateRequest description]
     * @param  string  $api
     * @param  array  $data
     * @return \GuzzleHttp\Psr7\Response
     */
    public function privateRequest(string $api, array $data = []) : Response
    {
        $uri = self::BASE_URI.trim($api, '/');
        $payload = new Payload($uri, $data);
        $headers = [
            'Content-Type' => 'text/plain',
            'Content-Length' => 0,
            'X-GEMINI-APIKEY' => $this->key,
            'X-GEMINI-PAYLOAD' => $payload->encode(),
            'X-GEMINI-SIGNATURE' => Signature::generate($payload, $this->secret),
        ];

        $request = $this->messageFactory->createRequest('POST', $uri, $headers);

        return $this->client->sendRequest($request);
    }
}
