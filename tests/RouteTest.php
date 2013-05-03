<?php namespace OrnoTest;

use Orno\Mvc\Route\Route;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    public function testInputAndOutput()
    {
        $route = new Route('/home/test', 'Application\TestController', 'testAction', 'get');

        $this->assertSame(['home', 'test'], $route->getUriSegments());
        $this->assertSame('Application\TestController', $route->getController());
        $this->assertSame('/home/test', $route->getRoute());
        $this->assertSame('testAction', $route->getAction());
        $this->assertSame('GET', $route->getMethod());
        $this->assertSame('Application', $route->getModule());
        $this->assertFalse($route->isClosure());
    }
}
