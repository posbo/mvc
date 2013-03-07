<?php namespace Orno\Tests;

use PHPUnit_Framework_Testcase;
use Orno\Mvc\Route\RouteCollection;
use Orno\Mvc\Route\Dispatcher;

class DispatcherTest extends PHPUnit_Framework_Testcase
{
    public function testMatchOnLiteral()
    {
        $route = new RouteCollection;

        $route->get('/', 'TestController@testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->setEnvironment(['SCRIPT_NAME' => '/index.php', 'REQUEST_URI' => '/index.php', 'REQUEST_METHOD' => 'GET']);

        $this->assertTrue($dispatch->match('GET'));
    }

    public function testMatchOnRequiredSegment()
    {
        $route = new RouteCollection;

        $route->get('/test/(required)', 'TestController@testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->setEnvironment(['SCRIPT_NAME' => '/index.php', 'REQUEST_URI' => '/test/somesegment', 'REQUEST_METHOD' => 'GET']);

        $this->assertTrue($dispatch->match('GET'));
    }

    public function testMatchOnMultipleRequiredSegments()
    {
        $route = new RouteCollection;

        $route->get('/test/(required)/(required2)', 'TestController@testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->setEnvironment(['SCRIPT_NAME' => '/index.php', 'REQUEST_URI' => '/test/somesegment/somesegment2', 'REQUEST_METHOD' => 'GET']);

        $this->assertTrue($dispatch->match('GET'));
    }

    public function testMatchOnRequiredAndPresentOptionalSegment()
    {
        $route = new RouteCollection;

        $route->get('/test/(required)(/optional)', 'TestController@testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->setEnvironment(['SCRIPT_NAME' => '/index.php', 'REQUEST_URI' => '/test/somesegment/somesegment2', 'REQUEST_METHOD' => 'GET']);

        $this->assertTrue($dispatch->match('GET'));
    }

    public function testMatchOnRequiredAndMissingOptionalSegment()
    {
        $route = new RouteCollection;

        $route->get('/test/(required)(/optional)', 'TestController@testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->setEnvironment(['SCRIPT_NAME' => '/index.php', 'REQUEST_URI' => '/test/somesegment', 'REQUEST_METHOD' => 'GET']);

        $this->assertTrue($dispatch->match('GET'));
    }

    public function testMatchOnOptionalSegment()
    {
        $route = new RouteCollection;

        $route->get('/test(/optional)', 'TestController@testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->setEnvironment(['SCRIPT_NAME' => '/index.php', 'REQUEST_URI' => '/test/somesegment', 'REQUEST_METHOD' => 'GET']);

        $this->assertTrue($dispatch->match('GET'));
    }

    public function testMatchOnOptionalMissingSegment()
    {
        $route = new RouteCollection;

        $route->get('/test(/optional)', 'TestController@testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->setEnvironment(['SCRIPT_NAME' => '/index.php', 'REQUEST_URI' => '/test', 'REQUEST_METHOD' => 'GET']);

        $this->assertTrue($dispatch->match('GET'));
    }
}
