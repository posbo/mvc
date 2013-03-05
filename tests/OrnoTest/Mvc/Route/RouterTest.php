<?php namespace OrnoTest\Mvc\Route;

use PHPUnit_Framework_Testcase;
use Closure;
use Orno\Mvc\Route\Router;
use Orno\Mvc\Route\Map;

class RouterTest extends PHPUnit_Framework_Testcase
{
    public function testRouteMatchingLiteralRoute()
    {
        $map = new Map;
        $map->route('/test/route/1', 'Controller@action');

        $router = new Router($map, '/test/route/1', 'GET');

        $this->assertTrue($router->match());
        $this->assertSame($router->getMatched(), '/test/route/1');
    }

    public function testRouteMatchingWildcardRoute()
    {
        $map = new Map;
        $map->route('/test/(id)', 'Controller@action');

        $router = new Router($map, '/test/1', 'GET');

        $this->assertTrue($router->match());
        $this->assertSame($router->getMatched(), '/test/(id)');
    }

    public function testRouteMatchingMultipleWildcardRoutes()
    {
        $map = new Map;
        $map->route('/test/(action)/(id)', 'Controller@action');

        $router = new Router($map, '/test/route/1', 'GET');

        $this->assertTrue($router->match());
        $this->assertSame($router->getMatched(), '/test/(action)/(id)');
    }
}
