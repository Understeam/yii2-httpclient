# Yii2 HTTP client

[![Build Status](https://travis-ci.org/Understeam/yii2-httpclient.svg?branch=master)](https://travis-ci.org/Understeam/yii2-httpclient)
[![Total Downloads](https://poser.pugx.org/understeam/yii2-httpclient/downloads)](https://packagist.org/packages/understeam/yii2-httpclient)

## Installation

Recommended way to install this extenstion is through Composer:

```bash
php composer.phar require understeam/yii2-httpclient:~1.0 --prefer-dist
```

## Configuration

Add this lines to your config file:

```php
...
'components' => [
	'httpclient' => [
		'class' =>'understeam\httpclient\Client',
		'detectMimeType' => true, // automatically transform request to data according to response Content-Type header
		'requestOptions' => [
		    // see guzzle request options documentation
		],
		'requestHeaders' => [
		    // specify global request headers (can be overrided with $options on making request)
		],
	],
],
...
```

## Basic usage

Performing HTTP GET request with mime type detection:
```php
// Result is html text
$text = Yii::$app->httpclient->get('http://httpbin.org/html');

// Result is SimpleXMLElement containing parsed XML
$xml = Yii::$app->httpclient->get('http://httpbin.org/xml');

// Result is parsed JSON array
$json = Yii::$app->httpclient->get('http://httpbin.org/get');

```

You can disable this behavior by specifying `$detectMimeType` option to whole component or single call

```php
// Result is Guzzle `Response` object
$text = Yii::$app->httpclient->get('http://httpbin.org/xml', [], false);

```

Make request with custom options:

```php
$text = Yii::$app->httpclient->get('http://httpbin.org/xml', [
    'proxy' => 'tcp://localhost:8125'
]);
```

Read more about this options in [Guzzle 6 documentation](http://guzzle.readthedocs.org/en/latest/request-options.html)

## HTTP methods

You can make request with several ways:

1. Call shortcut method (`get()`, `post()`, `put()`, `delete()`, etc.)
2. Call `request()` method

All shortcut methods has the same signature except `get()`:

```php
// Synchronous GET request
Yii::$app->httpclient->get(
    $url, // URL
    [], // Options
    true // Detect Mime Type?
);

// Synchronous POST (and others) request
Yii::$app->httpclient->post(
    $url, // URL
    $body, // Body
    [], // Options
    true // Detect Mime Type?
);

// Asynchronous GET request
Yii::$app->httpclient->getAsync(
    $url, // URL
    [] // Options
);

// Asynchronous POST (and others) request
Yii::$app->httpclient->postAsync(
    $url, // URL
    $body, // Body
    [] // Options
);

```

> __NOTE__: you still can make a GET request with body via `request()` function

## Asynchronous calls

To make an asynchronous request simly add `Async` to end of request method:

```php
// PromiseInterface
$promise = Yii::$app->httpclient->postAsync('http://httpbin.org/post');
```

> __NOTE__: mime type detection is not supported for asynchronous calls

Read more about asynchronous requests in [Guzzle 6 documentation](http://guzzle.readthedocs.org/en/latest/quickstart.html#async-requests)

## Request body

Types you can pass as a body of request:

1. __Arrayable object__ (ActiveRecord, Model etc.) - will be encoded into JSON object
2. __Array__ - will be sent as form request (x-form-urlencoded)

Any other data passed as body will be sent into Guzzle without any transformations.

Read more about request body in [Guzzle documentation](http://guzzle.readthedocs.org/en/latest/request-options.html#body)

## Appendix

Feel free to send feature requests and fix bugs with Pull Requests
