<?php

namespace Samrap\Gemini;

class Payload
{
    /**
     * The request endpoint.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * An associative array of data specific to the payload.
     *
     * @var array
     */
    protected $data;

    /**
     * The nonce for this payload.
     *
     * @var string
     */
    protected $nonce;

    /**
     * Create a new instance.
     */
    public function __construct(string $endpoint, array $data = [])
    {
        $this->endpoint = $endpoint;
        $this->data = $data;
        $this->nonce = (string) round(microtime(true));
    }

    /**
     * Get the generated nonce for this payload.
     *
     * @return string
     */
    public function getNonce() : string
    {
        return $this->nonce;
    }

    /**
     * Convert the payload to an array.
     *
     * @return array
     */
    public function toArray() : array
    {
        return array_merge($this->data, [
            'request' => $this->endpoint,
            'nonce' => $this->nonce,
        ]);
    }
}
