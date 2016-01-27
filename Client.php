<?php
/**
 * @link https://github.com/AnatolyRugalev
 * @copyright Copyright (c) AnatolyRugalev
 * @license https://tldrlegal.com/license/gnu-general-public-license-v3-(gpl-3)
 */

namespace understeam\httpclient;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\XmlParseException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Message\ResponseInterface;
use InvalidArgumentException;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use GuzzleHttp\ClientInterface;

/**
 * Client class is an interface designed for performing flexible HTTP requests
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

    /**
     * Returns GuzzleHttp client
     * @return ClientInterface GuzzleHttp client
     */
    public function getClient()
    {
        if(is_array($this->client))  {
            if(!isset($this->client['class'])) {
                $this->client['class'] = "\\GuzzleHttp\\Client";
            }
            $class = $this->client['class'];
            unset($this->client['class']);
            $this->client = new $class($this->client);
        }
        return $this->client;
    }

    public function formatResponse(ResponseInterface $response)
    {
        $contentType = $response->getHeader('Content-Type');
        if(strpos($contentType, 'json') !== false) {
            try {
                return $response->json();
            } catch(InvalidArgumentException $e) {
                return false;
            }
        }
        if(strpos($contentType, 'xml')) {
            try {
                return $response->xml();
            } catch(XmlParseException $e) {
                return false;
            }
        }
        return (string)$response->getBody();
    }

    /**
     * @param string $url
     * @param string $method
     * @param callable|null $beforeRequest
     * @param array $options
     * @return mixed
     */
    public function request($url, $method = 'GET', $beforeRequest = null, $options = [])
    {
        $format = !isset($options['format']) || $options['format'];
        unset($options['format']);
        $options = ArrayHelper::merge($this->requestOptions, $options);
        try {
            $request = $this->getClient()->createRequest($method, $url, $options);
            $event = new Event([
                'message' => $request,
            ]);
            if ($beforeRequest !== null) {
                if (call_user_func($beforeRequest, $event) === false) {
                    return false;
                }
            }
            $this->trigger(static::EVENT_BEFORE_REQUEST, $event);
            $response = $this->getClient()->send($request);
        } catch (RequestException $e) {
            throw $e;
        }
        $this->trigger(static::EVENT_AFTER_REQUEST, new Event([
            'message' => $response
        ]));
        if ($format) {
            return $this->formatResponse($response);
        } else {
            return $response;
        }
    }

}

