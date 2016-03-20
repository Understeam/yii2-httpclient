<?php

namespace Codeception\TestCase;

use understeam\httpclient\Client;
use Yii;
use yii\base\DynamicModel;

class ClientTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testConfig()
    {
        expect("Component is configured", $this->getClient() instanceof Client)->true();
    }

    public function testHtmlRequest()
    {
        $html = $this->getClient()->get('http://httpbin.org/html');
        expect("Downloaded content is string", is_string($html))->true();
        $response = $this->getClient()->get('http://httpbin.org/html', [], false);
        expect("Response is ResponseInterface object", $response)->isInstanceOf('Psr\Http\Message\ResponseInterface');
        expect("Content type is text/html", $response->getHeader('content-type'))->same(['text/html; charset=utf-8']);
    }

    public function testMethodRequests()
    {
        $methods = [
            'post',
            'put',
            'delete',
        ];
        foreach ($methods as $method) {
            $url = "http://httpbin.org/{$method}";
            $json = $this->getClient()->{$method}($url);
            expect("Downloaded content is array", is_array($json))->true();
            $response = $this->getClient()->{$method}($url, null, [], false);
            expect("Response is ResponseInterface object", $response)->isInstanceOf('Psr\Http\Message\ResponseInterface');
            expect("Content type is application/json", $response->getHeader('content-type'))->same(['application/json']);
            $responseData = $this->getClient()->formatResponse($response);
            expect("Downloaded content is array", is_array($responseData))->true();
        }
    }

    public function testMethodsAsyncRequests()
    {
        $methods = [
            'get',
            'post',
            'put',
            'delete',
        ];
        foreach ($methods as $method) {
            $url = "http://httpbin.org/{$method}";
            $promise = $this->getClient()->{$method . "Async"}($url);
            expect("Response is PromiseInterface object", $promise)->isInstanceOf('GuzzleHttp\Promise\PromiseInterface');
            $response = $promise->wait();
            expect("Response is ResponseInterface object", $response)->isInstanceOf('Psr\Http\Message\ResponseInterface');
            expect("Content type is application/json", $response->getHeader('content-type'))->same(['application/json']);
        }
    }

    public function testHeadersRequest()
    {
        $url = "http://httpbin.org/headers";
        $data = $this->getClient()->get($url, [
            'headers' => [
                'X-Test-Header' => 'test-header',
            ]
        ]);
        expect("Downloaded content has 'headers' key", $data)->hasKey('headers');
        expect("Downloaded content has 'headers.X-Test-Header' key", $data['headers'])->hasKey('X-Test-Header');
    }

    public function testObjectRequest()
    {
        $url = "http://httpbin.org/post";
        $attributes = [
            'field1' => '1',
            'field2' => '2',
        ];
        $data = $this->getClient()->post($url, new DynamicModel(['field1', 'field2'], $attributes));
        expect("Downloaded content has 'json' key", $data)->hasKey('json');
        expect("Downloaded content 'json' is valid", $data['json'])->same($attributes);
    }

    public function testFormRequest()
    {
        $url = "http://httpbin.org/post";
        $attributes = [
            'field1' => '1',
            'field2' => '2',
        ];
        $data = $this->getClient()->post($url, $attributes);
        expect("Downloaded content has 'form' key", $data)->hasKey('form');
        expect("Downloaded content 'form' is valid", $data['form'])->same($attributes);
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        return Yii::$app->get('httpclient');
    }

}