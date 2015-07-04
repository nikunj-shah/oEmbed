<?php

namespace Bangpound\oEmbed\Test\Provider;

use Bangpound\oEmbed\Provider\DiscoverProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DiscoverProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideSupport
     *
     * @param ResponseInterface $response
     * @param array             $params
     * @param $expected
     */
    public function testSupport(
      ResponseInterface $response,
      array $params = array(),
      $expected
    ) {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
          $response,
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $provider = new DiscoverProvider($client);
        $this->assertEquals($expected, $provider->supports('', $params));
    }

    /**
     * @dataProvider provideRequest
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param $url
     * @param array $params
     * @param $expected
     */
    public function testRequest(ResponseInterface $response, $url, array $params = array(), RequestInterface $expected)
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
          $response,
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $provider = new DiscoverProvider($client);
        $request = $provider->request($url, $params);
        $this->assertInstanceOf(get_class($expected), $request);
        $this->assertEquals($expected->getMethod(), $request->getMethod());

        $uri = $request->getUri();
        $query = Psr7\parse_query($uri->getQuery());

        $this->assertEquals($url, $query['url']);
    }

    /**
     * @dataProvider provideSupportWithMap
     *
     * @param ResponseInterface $response
     * @param array             $params
     * @param array             $map
     * @param $expected
     */
    public function testSupportWithMap(
      ResponseInterface $response,
      array $params = array(),
      array $map = null,
      $expected
    ) {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
          $response,
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $provider = new DiscoverProvider($client, $map);
        $this->assertEquals($expected, $provider->supports('', $params));
    }

    /**
     * @dataProvider provideRequestWithMap
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param $url
     * @param array                              $params
     * @param array                              $map
     * @param \Psr\Http\Message\RequestInterface $expected
     */
    public function testRequestWithMap(
      ResponseInterface $response,
      $url,
      array $params = array(),
      array $map = null,
      RequestInterface $expected)
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
          $response,
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $provider = new DiscoverProvider($client, $map);
        $request = $provider->request($url, $params);
        $this->assertInstanceOf(get_class($expected), $request);
        $this->assertEquals($expected->getMethod(), $request->getMethod());

        $uri = $request->getUri();
        $query = Psr7\parse_query($uri->getQuery());

        $this->assertEquals($url, $query['url']);
    }

    public function provideSupport()
    {
        return array_map(function ($value) {
            return array(
              $value['response'],
              $value['params'],
              $value['supports'],
            );
        }, self::fixture());
    }

    public function provideRequest()
    {
        return array_map(function ($value) {
            return array(
              $value['response'],
              $value['url'],
              $value['params'],
              $value['request'],
            );
        }, array_filter(self::fixture(), function ($value) {
            return $value['supports'];
        }));
    }

    public function provideSupportWithMap()
    {
        return array_map(function ($value) {
            return array(
              $value['response'],
              $value['params'],
              $value['map'],
              $value['supportsWithMap'],
            );
        }, self::fixture());
    }

    public function provideRequestWithMap()
    {
        return array_map(function ($value) {
            return array(
              $value['response'],
              $value['url'],
              $value['params'],
              $value['map'],
              $value['request'],
            );
        }, array_filter(self::fixture(), function ($value) {
            return $value['supportsWithMap'];
        }));
    }

    private static function fixture()
    {
        return array(
          [
            'url' => 'http://something.com/video/1',
            'params' => array(),
            'response' => new Psr7\Response(200, [], '<!DOCTYPE html><html><head><link rel="alternate" type="application/json+oembed" href="http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=json" title="Example Video JSON"><link rel="alternate" type="text/xml+oembed" href="http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=xml" title="Example Video XML"></head><body></body></html>'),
            'supports' => true,
            'supportsWithMap' => true,
            'map' => [
              'application/json+oembed' => 'json',
            ],
            'request' => new Psr7\Request('get', 'http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=json'),
          ],
          [
            'url' => 'http://something.com/video/1',
            'params' => array(),
            'response' => new Psr7\Response(200, [], '<!DOCTYPE html><html><head><link rel="alternate" type="application/json+oembed" href="http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=json" title="Example Video JSON"><link rel="alternate" type="text/xml+oembed" href="http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=xml" title="Example Video XML"></head><body></body></html>'),
            'supports' => true,
            'supportsWithMap' => true,
              'map' => [
                'application/json+oembed' => 'json',
              ],
            'request' => new Psr7\Request('get', 'http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=json'),
          ],
          [
            'url' => 'http://something.com/video/1',
            'response' => new Psr7\Response(200, [], '<!DOCTYPE html><html><head><link rel="alternate" type="application/json+oembed" href="http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=json" title="Example Video JSON"></head><body></body></html>'),
            'params' => ['format' => 'json'],
            'supports' => true,
            'supportsWithMap' => true,
            'map' => [
              'application/json+oembed' => 'json',
            ],
            'request' => new Psr7\Request('get', 'http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=json'),
          ],
          [
            'url' => 'http://something.com/video/1',
            'response' => new Psr7\Response(200, [], '<!DOCTYPE html><html><head><link rel="alternate" type="text/xml+oembed" href="http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=xml" title="Example Video XML"></head><body></body></html>'),
            'params' => ['format' => 'xml'],
            'supports' => true,
            'supportsWithMap' => true,
            'map' => [
              'text/xml+oembed' => 'xml',
            ],
            'request' => new Psr7\Request('get', 'http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=xml'),
          ],
          [
            'url' => 'http://something.com/video/1',
            'response' => new Psr7\Response(200, [], '<!DOCTYPE html><html><head><link rel="alternate" type="application/json+oembed" href="http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=json" title="Example Video JSON"></head><body></body></html>'),
            'params' => ['format' => 'xml'],
            'supports' => false,
            'supportsWithMap' => false,
            'map' => [
              'text/xml+oembed' => 'xml',
            ],
          ],
          [
            'url' => 'http://something.com/video/1',
            'response' => new Psr7\Response(200, [], '<!DOCTYPE html><html><head><link rel="alternate" type="text/xml+oembed" href="http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=xml" title="Example Video XML"></head><body></body></html>'),
            'params' => ['format' => 'json'],
            'supports' => false,
            'supportsWithMap' => false,
            'map' => [
              'application/json+oembed' => 'json',
            ],
          ],
          [
            'url' => 'http://something.com/video/1',
            'response' => new Psr7\Response(200, [], '<!DOCTYPE html><html><head><link rel="alternate" type="text/xml+oembed" href="http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=xml" title="Example Video XML"></head><body></body></html>'),
            'params' => ['format' => 'yaml'],
            'supports' => false,
            'supportsWithMap' => true,
            'map' => [
              'text/xml+oembed' => 'yaml',
            ],
            'request' => new Psr7\Request('get', 'http://example.com/oembed?url=http%3A%2F%2Fsomething.com%2Fvideo%2F1&amp;format=xml'),
          ],
          [
            'url' => '',
            'response' => new Psr7\Response(200, [], '<!DOCTYPE html><html><head></head><body></body></html>'),
            'params' => [],
            'supports' => false,
            'supportsWithMap' => false,
            'map' => [
            ],
          ],
          [
            'url' => '',
            'response' => new Psr7\Response(200, [], '<!DOCTYPE html><html><head><link rel="alternate" type="application/not+oembed" href="http://deadend.com"></head><body></body></html>'),
            'params' => [],
            'supports' => false,
            'supportsWithMap' => false,
            'map' => [
              'application/json+oembed' => 'json',
            ],
          ],
        );
    }
}
