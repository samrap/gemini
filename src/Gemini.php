<?php

namespace Samrap\Gemini;

use Http\Client\Exception\TransferException;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Samrap\Gemini\Exceptions\ClientException;
use Samrap\Gemini\Exceptions\GeminiException;

class Gemini implements PublicApi, PrivateApi
{
    /** @var string */
    const BASE_URI = 'https://api.gemini.com/v1/';

    /** @var string */
    const API_VERSION = 'v1';

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
     * The HTTP Client implementation.
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
    public function __construct(
        string $key = '',
        string $secret = '',
        HttpClient $client = null,
        MessageFactory $messageFactory = null
    ) {
        $this->key = $key;
        $this->secret = $secret;
        $this->client = $client ?: HttpClientDiscovery::find();
        $this->messageFactory = $messageFactory ?: MessageFactoryDiscovery::find();
    }

    /**
     * {@inheritdoc}
     */
    public function symbols() : array
    {
        return $this->publicRequest('symbols');
    }

    /**
     * {@inheritdoc}
     */
    public function ticker(string $symbol) : array
    {
        return $this->publicRequest('pubticker/'.$symbol);
    }

    /**
     * {@inheritdoc}
     */
    public function currentOrderBook(string $symbol, array $urlParameters = []) : array
    {
        return $this->publicRequest('book/'.$symbol, $urlParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function tradeHistory(string $symbol, array $urlParameters = []) : array
    {
        return $this->publicRequest('trades/'.$symbol, $urlParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function currentAuction(string $symbol) : array
    {
        return $this->publicRequest('auction/'.$symbol);
    }

    /**
     * {@inheritdoc}
     */
    public function auctionHistory(string $symbol, array $urlParameters = []) : array
    {
        return $this->publicRequest('auction/'.$symbol.'/history', $urlParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function newOrder(array $parameters) : array
    {
        return $this->privateRequest('order/new', $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function cancelOrder(array $parameters) : array
    {
        return $this->cancelOrder('order/cancel', $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function cancelAllSessionOrders() : array
    {
        return $this->privateRequest('order/cancel/session');
    }

    /**
     * {@inheritdoc}
     */
    public function cancelAllActiveOrders() : array
    {
        return $this->privateRequest('order/cancel/all');
    }

    /**
     * {@inheritdoc}
     */
    public function orderStatus(array $parameters) : array
    {
        return $this->privateRequest('order/status', $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveOrders() : array
    {
        return $this->privateRequest('orders');
    }

    /**
     * {@inheritdoc}
     */
    public function getPastTrades(array $parameters) : array
    {
        return $this->privateRequest('mytrades', $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getTradeVolume() : array
    {
        return $this->privateRequest('tradevolume');
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableBalances() : array
    {
        return $this->privateRequest('balances');
    }

    /**
     * {@inheritdoc}
     */
    public function newDepositAddress(string $currency, array $parameters = []) : array
    {
        return $this->privateRequest('deposit/'.$currency.'/newAddress', $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function withdrawCryptoFundsToWhitelistedAddress(string $currency, array $parameters) : array
    {
        return $this->privateRequest('withdraw/'.$currency, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function heartbeat() : array
    {
        return $this->privateRequest('heartbeat');
    }

    /**
     * Send a public request to the given API and return the respone JSON.
     *
     * @param  string  $api
     * @param  array  $urlParameters
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function publicRequest(string $api, array $urlParameters = []) : array
    {
        $uri = self::BASE_URI.trim($api, '/');

        if (! empty($urlParameters)) {
            $uri .= '?'.http_build_query($urlParameters);
        }

        $request = $this->messageFactory->createRequest('GET', $uri);

        return $this->getResponseJson($this->send($request));
    }

    /**
     * Send a private request to the given API and return the response JSON.
     *
     * @param  string  $api
     * @param  array  $data
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function privateRequest(string $api, array $data = []) : array
    {
        $uri = self::BASE_URI.trim($api, '/');
        $endpoint = sprintf('/%s/%s', self::API_VERSION, trim($api, '/'));
        $payload = new Payload($endpoint, $data);
        $request = $this->messageFactory->createRequest('POST', $uri, [
            'Content-Type' => 'text/plain',
            'Content-Length' => 0,
            'X-GEMINI-APIKEY' => $this->key,
            'X-GEMINI-PAYLOAD' => $payload->encode(),
            'X-GEMINI-SIGNATURE' => Signature::generate($payload, $this->secret),
            'Cache-Control' => 'no-cache',
        ]);

        return $this->getResponseJson($this->send($request));
    }

    /**
     * Send a request and return the response.
     *
     * @param  \Psr\Http\Message\RequestInterface  $request
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function send(RequestInterface $request) : ResponseInterface
    {
        try {
            $response = $this->client->sendRequest($request);
        } catch (TransferException $exception) {
            throw new ClientException(
                'An error occurred while talking to the API.', 0, $exception
            );
        }

        if ($response->getStatusCode() !== 200) {
            throw $this->createExceptionFromResponse($response);
        }

        return $response;
    }

    /**
     * Get the JSON from the HTTP response as an associative array.
     *
     * @param  \Psr\Http\Message\ResponseInterface  $response
     * @return array
     */
    protected function getResponseJson(ResponseInterface $response) : array
    {
        $data = json_decode((string) $response->getBody(), true);

        return (is_array($data)) ? $data : [];
    }

    /**
     * Given an error response, convert the payload into the respective exception.
     *
     * @param  \Psr\Http\Message\ResponseInterface  $response
     * @return \Samrap\Gemini\Exceptions\GeminiException
     */
    protected function createExceptionFromResponse(ResponseInterface $response) : GeminiException
    {
        $payload = $this->getResponseJson($response);
        $exception = __NAMESPACE__."\\Exceptions\\{$payload['reason']}Exception";

        if (class_exists($exception)) {
            return new $exception($payload['message']);
        }

        return new GeminiException("[{$payload['reason']}] {$payload['message']}");
    }
}
