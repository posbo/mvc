<?php namespace Orno\Tests;

use PHPUnit_Framework_Testcase;
use Orno\Mvc\Route\Route;

class RouteTest extends PHPUnit_Framework_Testcase
{
    public function testRouteSetsMembersWithController()
    {
        $route = new Route('/test/(route)', 'Some\Namespace\Controller', 'someAction', 'GET');

        $this->assertSame($route->getRoute(), '/test/(route)');
        $this->assertSame($route->getController(), 'Some\Namespace\Controller');
        $this->assertSame($route->getAction(), 'someAction');
        $this->assertSame($route->getMethod(), 'GET');
        $this->assertFalse($route->isClosure());
    }

    public function testRouteSetsSegments()
    {
        $route = new Route('/test/(route)');
        $this->assertSame($route->getSegments()[0], 'test');
        $this->assertSame($route->getSegments()[1], '(route)');

        $route2 = new Route('test/(route)');
        $this->assertSame($route2->getSegments()[0], 'test');
        $this->assertSame($route2->getSegments()[1], '(route)');

        $route3 = new Route('test/(route)/');
        $this->assertSame($route3->getSegments()[0], 'test');
        $this->assertSame($route3->getSegments()[1], '(route)');

        $route4 = new Route('/test/(route)/');
        $this->assertSame($route4->getSegments()[0], 'test');
        $this->assertSame($route4->getSegments()[1], '(route)');
    }
}
