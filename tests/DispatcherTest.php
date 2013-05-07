<?php namespace OrnoTest;

use PHPUnit_Framework_TestCase;
use Orno\Mvc\Route\Dispatcher;

class DispatcherTest extends PHPUnit_Framework_TestCase
{
    protected $dispatcher;

    public function setUp()
    {
        $collection = $this->getMock('Orno\Mvc\Route\RouteCollection');

        $collection->expects($this->any())
                   ->method('getRoutes')
                   ->will($this->returnValue($this->getMockRoutes()));

        $this->dispatcher = new Dispatcher($collection);
    }

    public function tearDown()
    {
        unset($this->dispatcher);
    }

    public function testMatcheReturnsTrueOnWildcardMatch()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue('GET'));

        $request->expects($this->any())
                ->method('getPathInfo')
                ->will($this->returnValue('/test'));

        $this->assertTrue($this->dispatcher->match($request));
    }

    public function testMatcheReturnsTrueOnMultipleWildcardMatch()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue('GET'));

        $request->expects($this->any())
                ->method('getPathInfo')
                ->will($this->returnValue('/test/1234/test2'));

        $this->assertTrue($this->dispatcher->match($request));
    }

    public function testMatchReturnsTrueOnExactMatch()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue('GET'));

        $request->expects($this->any())
                ->method('getPathInfo')
                ->will($this->returnValue('/'));

        $this->assertTrue($this->dispatcher->match($request));
    }

    public function testMatchReturnsFalseOnFailure()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue('GET'));

        $request->expects($this->any())
                ->method('getPathInfo')
                ->will($this->returnValue('/no-route'));

        $this->assertFalse($this->dispatcher->match($request));
    }

    public function testGetArgumentsReturnsCorrectArray()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue('GET'));

        $request->expects($this->any())
                ->method('getPathInfo')
                ->will($this->returnValue('/test/argument'));

        $this->dispatcher->match($request);

        $array = ['argument'];

        $this->assertSame($array, $this->dispatcher->getArguments());
    }

    public function testGetArgumentsReturnsNullKeyForOptionalArgument()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue('GET'));

        $request->expects($this->any())
                ->method('getPathInfo')
                ->will($this->returnValue('/test'));

        $this->dispatcher->match($request);

        $array = [null];

        $this->assertSame($array, $this->dispatcher->getArguments());
    }

    public function testGetRouteThrowsExceptionWhenNoRouteMatched()
    {
        $this->setExpectedException('Orno\Mvc\Route\Exception\RouteNotMatchedException');
        $this->dispatcher->getRoute();
    }

    public function testDispatchThrowsExceptionWhenNoRouteMatched()
    {
        $this->setExpectedException('Orno\Mvc\Route\Exception\RouteNotMatchedException');
        $this->dispatcher->dispatch();
    }

    public function testDispatchReturnsResponse()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue('GET'));

        $request->expects($this->any())
                ->method('getPathInfo')
                ->will($this->returnValue('/'));

        $this->dispatcher->match($request);

        $controller = $this->getMock('Controller', ['action']);

        $controller->expects($this->once())
                   ->method('action')
                   ->will($this->returnValue('Hello World'));

        $container = $this->getMock('Orno\Di\Container');

        $container->expects($this->once())
                  ->method('resolve')
                  ->will($this->returnValue($controller));

        $this->dispatcher->setContainer($container);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $this->dispatcher->dispatch());
    }

    public function getMockRoutes()
    {
        $route1 = $this->getMock('Orno\Mvc\Route\Route');
        $route2 = $this->getMock('Orno\Mvc\Route\Route');
        $route3 = $this->getMock('Orno\Mvc\Route\Route');
        $route4 = $this->getMock('Orno\Mvc\Route\Route');

        $route1->expects($this->any())
               ->method('getRoute')
               ->will($this->returnValue('/'));

        $route1->expects($this->any())
               ->method('getController')
               ->will($this->returnValue('Controller'));

        $route1->expects($this->any())
               ->method('getAction')
               ->will($this->returnValue('action'));

        $route1->expects($this->any())
               ->method('isClosure')
               ->will($this->returnValue(false));

        $route1->expects($this->any())
               ->method('getUriSegments')
               ->will($this->returnValue([]));

        $route2->expects($this->any())
               ->method('getRoute')
               ->will($this->returnValue('/test(\/.+?)?'));

        $route2->expects($this->any())
               ->method('getUriSegments')
               ->will($this->returnValue(['test', '(?any)']));

        $route3->expects($this->any())
               ->method('getRoute')
               ->will($this->returnValue('/test/(.+?)?'));

        $route3->expects($this->any())
               ->method('getUriSegments')
               ->will($this->returnValue(['test', '(any)']));

        $route4->expects($this->any())
               ->method('getRoute')
               ->will($this->returnValue('/test/(.+?)?/test2(\/.+?)?'));

        $route4->expects($this->any())
               ->method('getUriSegments')
               ->will($this->returnValue(['test', '(any)', 'test2', '(?any)']));

        return [
            'ANY' => [$route1, $route2, $route3, $route4],
            'GET' => [],
            'POST' => [$route1, $route2],
            'PUT' => [],
            'PATCH' => [],
            'DELETE' => [],
            'OPTIONS' => []
        ];
    }
}
