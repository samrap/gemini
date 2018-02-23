<?php

namespace Samrap\Gemini;

interface PrivateApi
{
    /**
     * Create a new order.
     *
     * @param  array  $parameters
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function newOrder(array $parameters) : array;

    /**
     * Cancel an order.
     *
     * @param  array  $parameters
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function cancelOrder(array $parameters) : array;

    /**
     * This will cancel all orders opened by this session.
     *
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function cancelAllSessionOrders() : array;

    /**
     * This will cancel all outstanding orders created by all sessions owned by
     * this account, including interactive orders placed through the UI.
     *
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function cancelAllActiveOrders() : array;

    /**
     * Gets the status for an order.
     *
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function orderStatus(array $parameters) : array;

    /**
     * Get active orders.
     *
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function getActiveOrders() : array;

    /**
     * Get past trades.
     *
     * @param  array  $parameters
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function getPastTrades(array $parameters) : array;

    /**
     * Get trade volume.
     *
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function getTradeVolume() : array;

    /**
     * This will show the available balances in the supported currencies.
     *
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function getAvailableBalances() : array;

    /**
     * This will create a new cryptocurrency deposit address with an optional label.
     *
     * @param  string  $currency
     * @param  array  $parameters
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function newDepositAddress(
        string $currency,
        array $parameters = []
    ) : array;

    /**
     * Withdraw crypto funds to a whitelisted address.
     *
     * @param  array  $parameters
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function withdrawCryptoFundsToWhitelistedAddress(
        string $currency,
        array $parameters
    ) : array;

    /**
     * This will prevent a session from timing out and canceling orders if the
     * require heartbeat flag has been set.
     *
     * Note that this is only required if no other private API requests have
     * been made. The arrival of any message resets the heartbeat timer.
     *
     * @throws \Samrap\Gemini\Exceptions\GeminiException
     * @return array
     */
    public function heartbeat() : array;
}
