<?php

namespace Samrap\Gemini;

class Signature
{
    /**
     * Generate a keyed hash value for the private request signature.
     *
     * @param  \Samrap\Gemini\Payload  $payload
     * @param  string  $key
     * @return string
     */
    public static function generate(Payload $payload, string $key) : string
    {
        return hash_hmac('sha384', $payload->encode(), $key);
    }
}
