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
     * @var int
     */
    protected $nonce;

    /**
     * Create a new instance.
     */
    public function __construct(string $endpoint, array $data = [])
    {
        $this->endpoint = $endpoint;
        $this->data = $data;
        $this->nonce = round(microtime(true));
    }

    /**
     * Get the generated nonce for this payload.
     *
     * @return int
     */
    public function getNonce() : int
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
        return array_merge([
            'request' => $this->endpoint,
            'nonce' => $this->nonce,
        ], $this->data);
    }

    public function toJson() : string
    {
        return json_encode($this->toArray());
    }
}
