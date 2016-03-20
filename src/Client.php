<?php
/**
 * @link https://github.com/AnatolyRugalev
 * @copyright Copyright (c) AnatolyRugalev
 * @license https://tldrlegal.com/license/gnu-general-public-license-v3-(gpl-3)
 */

namespace understeam\httpclient;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Yii;
use yii\base\Arrayable;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Client class is an interface designed for performing flexible HTTP requests
 *
 * Following shortcuts available via magic __call method:
 *
 * @method get($url, $options = [], $detectMimeType = null) makes GET request to given url. see [[request()]] for arguments explanation
 * @method getAsync($url, $options = []) makes asynchronous GET request to given url. see [[requestAsync()]] for arguments explanation
 * @method post($url, $body = null, $options = [], $detectMimeType = null) makes POST request to given url. see [[request()]] for arguments explanation
 * @method postAsync($url, $body = null, $options = []) makes asynchronous POST request to given url. see [[requestAsync()]] for arguments explanation
 * @method put($url, $body = null, $options = [], $detectMimeType = null) makes PUT request to given url. see [[request()]] for arguments explanation
 * @method putAsync($url, $body = null, $options = []) makes asynchronous PUT request to given url. see [[requestAsync()]] for arguments explanation
 * @method delete($url, $body = null, $options = [], $detectMimeType = null) makes DELETE request to given url. see [[request()]] for arguments explanation
 * @method deleteAsync($url, $body = null, $options = []) makes asynchronous DELETE request to given url. see [[requestAsync()]] for arguments explanation
 * @method options($url, $body = null, $options = [], $detectMimeType = null) makes OPTIONS request to given url. see [[request()]] for arguments explanation
 * @method optionsAsync($url, $body = null, $options = []) makes asynchronous OPTIONS request to given url. see [[requestAsync()]] for arguments explanation
 * @method head($url, $body = null, $options = [], $detectMimeType = null) makes HEAD request to given url. see [[request()]] for arguments explanation
 * @method headAsync($url, $body = null, $options = []) makes asynchronous HEAD request to given url. see [[requestAsync()]] for arguments explanation
 *
 * You can make any other HTTP request in same manner
 *
 * @author Anatoly Rugalev
 * @link https://github.com/AnatolyRugalev
 */
class Client extends Component
{

    const EVENT_BEFORE_REQUEST = 'beforeRequest';
    const EVENT_AFTER_REQUEST = 'afterRequest';

    /**
     * @var array|ClientInterface GuzzleHttp config and instance
     */
    public $client = [];

    public $requestOptions = [];

    public $baseUrl;

    public $requestHeaders = [];

    public $detectMimeType = true;

    public $httpVersion = '1.1';

    /**
     * Returns GuzzleHttp client
     * @return ClientInterface GuzzleHttp client
     */
    public function getClient()
    {
        if (is_array($this->client)) {
            if (!isset($this->client['class'])) {
                $this->client['class'] = "\\GuzzleHttp\\Client";
            }
            $this->client = Yii::createObject($this->client);
        }
        return $this->client;
    }

    public function __call($method, $args)
    {
        if (!isset($args[0])) {
            throw new InvalidParamException("Url is not specified");
        }
        $methodName = $method;
        $request = 'request';
        if (substr($methodName, -5) === 'Async') {
            $methodName = substr($methodName, 0, -5);
            $request .= 'Async';
        }
        $methodName = strtoupper(implode('-', preg_split('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', $methodName)));
        $url = $args[0];
        if ($methodName === 'GET') {
            $body = null;
            $options = isset($args[1]) ? $args[1] : [];
            $detectMimeType = isset($args[2]) ? $args[2] : true;
        } else {
            $body = isset($args[1]) ? $args[1] : null;
            $options = isset($args[2]) ? $args[2] : [];
            $detectMimeType = isset($args[3]) ? $args[3] : true;
        }
        return $this->$request($methodName, $url, $body, $options, $detectMimeType);
    }

    public static function serialize($body, &$options)
    {
        $options['headers']['content-type'] = 'application/json';
        if ($body instanceof Arrayable) {
            return Json::encode($body->toArray());
        } else {
            return Json::encode($body);
        }
    }

    protected function prepareOptions(&$options)
    {
        $options = ArrayHelper::merge($this->requestOptions, $options);
        if (isset($options['headers'])) {
            $options['headers'] = ArrayHelper::merge($options['headers'], $this->requestHeaders);
        } else {
            $options['headers'] = $this->requestHeaders;
        }
    }

    protected function prepareBody($body, &$options)
    {
        if (is_scalar($body)) {
            return $body;
        }
        if (is_array($body)) {
            $options['form_params'] = $body;
            return null;
        }
        if (is_object($body)) {
            $options['headers']['content-type'] = 'application/json';
            if ($body instanceof Arrayable) {
                return Json::encode($body->toArray());
            } else {
                return Json::encode($body);
            }
        }
        return $body;
    }

    public function formatResponse(ResponseInterface $response)
    {
        $contentType = $response->getHeader('Content-Type');
        if (sizeof($contentType)) {
            $contentType = array_shift($contentType);
            if (preg_match('/^([a-z-]+\/[a-z-]+)/', $contentType, $matches)) {
                $mimeType = $matches[1];
            } else {
                $mimeType = null;
            }
        } else {
            $mimeType = null;
        }
        switch ($mimeType) {
            case 'application/json':
                try {
                    return Json::decode((string)$response->getBody());
                } catch (InvalidParamException $e) {
                    return false;
                }
            case 'application/xml':
            case 'application/atom+xml':
            case 'application/soap+xml':
            case 'application/xhtml+xml':
            case 'application/xml-dtd':
            case 'application/xop+xml':
            case 'text/xml':
                return simplexml_load_string((string)$response->getBody());
        }
        return (string)$response->getBody();
    }

    public function createRequest($method, $url, $body = null, $headers = [])
    {
        return new Request($method, $url, ArrayHelper::merge($this->requestHeaders, $headers), $body, $this->httpVersion);
    }

    public function request($method, $url, $body = null, $options = [], $detectMimeType = null)
    {
        $body = $this->prepareBody($body, $options);
        $request = $this->createRequest($method, $url, $body);
        return $this->send($request, $options, $detectMimeType);
    }

    public function requestAsync($method, $url, $body = null, $options = [])
    {
        $body = $this->prepareBody($body, $options);
        $request = $this->createRequest($method, $url, $body);
        return $this->sendAsync($request, $options);
    }

    public function beforeRequest(RequestInterface $request)
    {
        $event = new Event([
            'message' => $request,
        ]);
        $this->trigger(static::EVENT_BEFORE_REQUEST, $event);
        return $event->isValid;
    }

    public function afterRequest(ResponseInterface $response)
    {
        $this->trigger(static::EVENT_AFTER_REQUEST, new Event([
            'message' => $response
        ]));
    }

    public function send(RequestInterface $request, $options = [], $detectMimeType = null)
    {
        if (!$this->beforeRequest($request)) {
            return false;
        }
        $this->prepareOptions($options);
        $response = $this->getClient()->send($request, $options);
        $this->afterRequest($response);
        if ($detectMimeType === null) {
            $detectMimeType = $this->detectMimeType;
        }
        if ($detectMimeType) {
            return $this->formatResponse($response);
        } else {
            return $response;
        }
    }

    public function sendAsync(RequestInterface $request, $options = [])
    {
        if (!$this->beforeRequest($request)) {
            return false;
        }
        $this->prepareOptions($options);
        $promise = $this->getClient()->sendAsync($request, $options);
        $promise->then([$this, 'afterRequest']);
        return $promise;
    }
}
