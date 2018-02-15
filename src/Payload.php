<?php

namespace Samrap\Gemini;

class Payload
{
    /**
     * The nonce for this payload.
     *
     * @var string
     */
    protected $nonce;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->nonce = (string) round(microtime(true));
    }

    /**
     * Get the generated nonce for this Payload.
     *
     * @return string
     */
    public function getNonce() : string
    {
        return $this->nonce;
    }
}
