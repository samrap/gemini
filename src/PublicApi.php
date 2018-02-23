<?php

namespace Samrap\Gemini;

interface PublicApi
{
    /**
     * This endpoint retrieves all available symbols for trading.
     *
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function symbols() : array;

    /**
     * This endpoint retrieves information about recent trading activity for the symbol.
     *
     * @param  string  $symbol
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function ticker(string $symbol) : array;

    /**
     * This will return the current order book, as two arrays, one of bids,
     * and one of asks.
     *
     * @param  string  $symbol
     * @param  array  $urlParameters
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function currentOrderBook(
        string $symbol,
        array $urlParameters = []
    ) : array;

    /**
     * This will return the trades that have executed since the specified
     * timestamp. Timestamps are either seconds or milliseconds since
     * the epoch (1970-01-01).
     *
     * @param  string  $symbol
     * @param  array  $urlParameters
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function tradeHistory(
        string $symbol,
        array $urlParameters = []
    ) : array;

    /**
     * [currentAuction description].
     *
     * @param  string  $symbol
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function currentAuction(string $symbol) : array;

    /**
     * This will return the auction events, optionally including publications
     * of indicative prices, since the specific timestamp.
     *
     * @param  string  $symbol
     * @param  array  $urlParameters
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function auctionHistory(
        string $symbol,
        array $urlParameters = []
    ) : array;
}
