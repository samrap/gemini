# Gemini API Client

[![Travis](https://img.shields.io/travis/samrap/gemini.svg?style=flat-square)](https://github.com/samrap/gemini)

---

**Note:** This is currently in early development and not yet ready for production purposes.

---

This is a PHP client implementation for the [Gemini](https://gemini.com/) digital asset exchange REST API. It is built on expressive contracts that mirror the available public and private API methods, with a decoupled HTTP implementation for the underlying requests. If you are looking to interact with the Gemini API without worrying about the underlying HTTP requests and responses, then this package is for you.

## Installation

In order to talk to the API, you will need to install a compatible HTTP client or adapter. This package utilizes HTTPlug which defines how HTTP messages should be sent and received. You can use any library to send HTTP messages
that implements [php-http/client-implementation](https://packagist.org/providers/php-http/client-implementation).

Here is a list of all officially supported clients and adapters by HTTPlug: http://docs.php-http.org/en/latest/clients.html

Read more about HTTPlug in [their docs](http://docs.php-http.org/en/latest/httplug/users.html).

---

**Note:** The [Guzzle6 Adapter](http://docs.php-http.org/en/latest/clients/guzzle6-adapter.html) will be used in the following examples.

---

Once you have chosen your HTTP implementation, go ahead and install it with this package:

```
composer require php-http/guzzle6-adapter samrap/gemini
```

That's all you have to do to get started. The client will automatically find your chosen HTTP implementation under the hood, so you can focus on the fun stuff!

## Usage

### Basic Usage

In order to talk to the API, you will need to create an instance of the `Samrap\Gemini\Gemini` class. This class implements the API contracts necessary for you to do everything the REST API has to offer.

The class takes two arguments, a `key` and `secret`, which are your API key and secret, respectively. Of course, if you only plan on using the [Public APIs](https://docs.gemini.com/rest-api/#symbols), you may ignore these arguments:

```php
use Samrap\Gemini\Gemini;

$key = 'mykey';
$secret = '1234abcd'
$gemini = new Gemini($key, $secret);
```

The Gemini client's methods map directly to the available APIs in the documentation. For example, if you want to get data from the [Symbols API](https://docs.gemini.com/rest-api/#symbols), simply call the `symbols` method on the Gemini client:

```php
$gemini = new Gemini();
$symbols = $gemini->symbols();

print_r($symbols); // ["btc-usd", "ethbtc", "ethusd"]
```

The return value of all API calls is the HTTP response's decoded JSON as an associative array. The Gemini client handles all the HTTP requests and responses, allowing you to focus on the data that matters most.

APIs with more than one word, such as [Current Order Book](https://docs.gemini.com/rest-api/#current-order-book), are called as _camelCase_ versions of themselves. Parameters in the URI scheme are passed as individual arguments, while URL (GET) parameters are given as an associative array. Let's take a look at what it would look like to access the public **Current Order Book** API:

```php
$gemini = new Gemini();
$symbol = 'ethusd';
$orderBook = $gemini->currentOrderBook($symbol, [
    'limit_bids' => 10,
    'limit_asks' => 10,
]);
```

As with all methods, the return value will be the decoded JSON from the response body. Optional URL parameters can be ignored. Since the `limit_bids` and `limit_asks` parameters are optional, we could just as easily write the following:

```php
$gemini = new Gemini();
$symbol = 'ethusd';
$orderBook = $gemini->currentOrderBook($symbol);
```

Piece of cake! 

### Private APIs

The Gemini client automatically handles authentication and request signing for you. This means that accessing the Private APIs (APIs that require a session) is just as easy as talking to the public ones. Let's place a [new buy order](https://docs.gemini.com/rest-api/#new-order):

```php
$gemini = new Gemini('mykey', '1234abcd');
$order = $gemini->newOrder([
    'client_order_id' => '20150102-4738721',
    'symbol' => 'btcusd',      
    'amount' => '34.12',       
    'price' => '622.13',
    'side' => 'buy',           
    'type' => 'exchange limit',
    'options' => ['maker-or-cancel'],
]);
```

That's it! If the request was successful, the value of `$order` will be an associative array of the response JSON. If an error occurred, the Gemini Client throws an exception representing the exact error. See [Error Handling](#error-handling) for more information.

### API Reference

The Gemini client implements two contracts, `Samrap\Gemini\PublicApi` and `Samrap\Gemini\PrivateApi`. These contracts contain the API methods and their parameters, should you need a reference.

### Error Handling

The Gemini client automatically converts all API errors into exceptions named after the **reason** in each [Error Payload](https://docs.gemini.com/rest-api/#error-payload). An `AuctionNotOpen` error will throw a `Samrap\Gemini\Exceptions\AuctionNotOpenException`, a `ClientOrderIdTooLong` will throw a `Samrap\Gemini\Exceptions\ClientOrderIdTooLongException`, etc. Every exception extends the `Samrap\Gemini\Exceptions\GeminiException`. This gives you great flexibility to handle specific errors you might expect, while adding a catch-all at the end. 

Imagine we are writing a method to allow users to check the status of their orders. A lot can go wrong. A user might enter the incorrect order ID, so we will need to account for that. Additionally, the API might be down for maintenance and we certainly would want to log that information. Just to be safe, we should log any other error that _could_ occur. Ok, let's write it:

```php

use Samrap\Gemini\Exceptions\GeminiException;
use Samrap\Gemini\Exceptions\MaintenanceException;
use Samrap\Gemini\Exceptions\OrderNotFoundException;

// ...

public function getOrderStatus($order_id)
{
    try {
        $status = $this->gemini->orderStatus([
            'order_id' => $order_id,
        ])
    } catch (OrderNotFoundException $e) {
        return 'The order you searched for does not exist.';
    } catch (MaintenanceException $e) {
        $this->logger->critical($e->getMessage());

        return 'The API is currently unavailable';
    } catch (GeminiException $e) {
        $this->logger->warn($e->getMessage())
    }

    return $status['original_amount'];
}
```

As you can see, this is much more expressive than dealing with response status codes or checking the error payload to figure out what to do next. The Gemini client handles all of this for you, allowing you to focus on the requirements of your application.

## More

More documentation is coming as development continues.
