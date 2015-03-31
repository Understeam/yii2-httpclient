<?php

namespace Codeception\TestCase;

use understeam\httpclient\Client;
use understeam\httpclient\Event;
use Yii;

class ClientTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testConfig()
    {
        expect("Component is configured", $this->getClient())->notNull();
    }

    public function testBeforeRequestEvent()
    {
        $eventTriggered = false;
        $client = $this->getClient();
        $client->on(Client::EVENT_BEFORE_REQUEST, function (Event $event) use (&$eventTriggered) {
            expect("Event message is not null", $event->message)->notNull();
            expect("Event message is Request object", get_class($event->message))->equals("GuzzleHttp\\Message\\Request");

            $eventTriggered = true;
        });
        $client->request("http://httpbin.org/");
        expect("Event is triggered", $eventTriggered)->true();
    }

    public function testBeforeRequestInlineEvent()
    {
        $eventTriggered = false;
        $client = $this->getClient();
        $client->request("http://httpbin.org/", 'GET', function (Event $event) use (&$eventTriggered) {
            expect("Event message is not null", $event->message)->notNull();
            expect("Event message is Request object", get_class($event->message))->equals("GuzzleHttp\\Message\\Request");

            $eventTriggered = true;
        });
        expect("Event is triggered", $eventTriggered)->true();
    }

    public function testAfterRequestInlineEvent()
    {
        $eventTriggered = false;
        $client = $this->getClient();
        $client->on(Client::EVENT_AFTER_REQUEST, function (Event $event) use (&$eventTriggered) {
            expect("Event message is not null", $event->message)->notNull();
            expect("Event message is Response object", get_class($event->message))->equals("GuzzleHttp\\Message\\Response");

            $eventTriggered = true;
        });
        $client->request("http://httpbin.org/");
        expect("Event is triggered", $eventTriggered)->true();

        $eventTriggered = false;
        $client->on(Client::EVENT_AFTER_REQUEST, function (Event $event) use (&$eventTriggered) {
            $eventTriggered = true;
        });
        $client->request("http://httpbin.org/", 'GET', function (Event $event) {
            return false; //stop request
        });
        expect("Event is not triggered", $eventTriggered)->false();
    }

    public function testXmlFormatting()
    {
        $client = $this->getClient();
        $result = $client->request('http://httpbin.org/xml');
        expect("Result is not false", $result)->notEquals(false);
        expect("Result is instance of SimpleXMLElement", get_class($result))->equals("SimpleXMLElement");
    }

    public function testJsonFormatting()
    {
        $client = $this->getClient();
        $result = $client->request('http://httpbin.org/ip');
        expect("Result is not false", $result)->notEquals(false);
        expect("Result is array", is_array($result))->true();
    }

    public function testRawFormatting()
    {
        $client = $this->getClient();
        $result = $client->request('http://httpbin.org/html');
        expect("Result is not false", $result)->notEquals(false);
        expect("Result is string", gettype($result))->equals('string');
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        return Yii::$app->get('httpclient');
    }

}