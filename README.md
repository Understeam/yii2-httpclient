# Yii2 HTTP client

[![Build Status](https://travis-ci.org/Understeam/yii2-httpclient.svg?branch=master)](https://travis-ci.org/Understeam/yii2-httpclient)

## Configuration

Add this lines to your config file:

```php
...
'components' => [
	'httpclient' => [
		'class' =>'understeam\httpclient\Client',
	],
],
...
```

## Basic usage

Performing HTTP GET request with format detection:
```php
// Result is html text
$text = Yii::$app->httpclient->request('http://httpbin.org');

// Result is SimpleXMLElement containing parsed XML
$xml = Yii::$app->httpclient->request('http://httpbin.org/xml');

// Result is parsed JSON array
$json = Yii::$app->httpclient->request('http://httpbin.org/ip');

```

To ignore response headers and content type detection pass `['format' => false]` to the `$options` argument:
```php
// Result is xml text
$text = Yii::$app->httpclient->request('http://httpbin.org/xml', 'GET', null, ['format' => false]);
```

To add post fields, uploading files and other stuff use third function parameter `$beforeRequest`:

```php
Yii::$app->httpclient->request($slackHookUrl, 'POST', function(Event $event) use ($payload) {
	// GuzzleHttp\Message\Request object
	$request = $event->message;
	$post = new PostBody();
	$post->setField('payload', $payload);
	$request->setBody($post);
	if($payload === null) {
		// This statement stops request processing
		return false;
	}
});
```

Also Client triggers 2 events: `beforeRequest` and `afterRequest`. `beforeRequest` acts the same as function callback and `afterRequest` has `GuzzleHttp\Message\Response` as `$message` property.

You can see some code in tests. Good luck.
