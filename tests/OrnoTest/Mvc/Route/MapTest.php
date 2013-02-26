<?php namespace OrnoTest\Mvc\Route;

use PHPUnit_Framework_Testcase;
use Closure;
use Orno\Mvc\Route\Map;

class MapTest extends PHPUnit_Framework_Testcase
{
    public function testMapRegistersRouteToCorrectMethod()
    {
        $route = new Map;

        $route->route('/', 'Controller');
        $route->get('/get', 'Controller@getAction');
        $route->post('/post', 'Controller@postAction');
        $route->put('/put', 'Controller@putAction');
        $route->delete('/delete', 'Controller@deleteAction');

        $map = $route->getMap();

        $this->assertTrue(array_key_exists('/', $map['GET']));
        $this->assertTrue(array_key_exists('/get', $map['GET']));
        $this->assertTrue(array_key_exists('/post', $map['POST']));
        $this->assertTrue(array_key_exists('/put', $map['PUT']));
        $this->assertTrue(array_key_exists('/delete', $map['DELETE']));
    }

    public function testMapRegistersCorrectControllerAndAction()
    {
        $route = new Map;

        $route->get('/one', 'ControllerOne@actionOne');
        $route->get('/two', 'ControllerTwo@actionTwo');

        $map = $route->getMap()['GET'];

        $this->assertSame('ControllerOne', $map['/one']['controller']);
        $this->assertSame('ControllerTwo', $map['/two']['controller']);
        $this->assertSame('actionOne', $map['/one']['action']);
        $this->assertSame('actionTwo', $map['/two']['action']);
    }

    public function testMapRegistersToJustController()
    {
        $route = new Map;

        $route->route('/restful-one', 'ControllerOne');
        $route->route('/restful-two', 'ControllerTwo');

        $map = $route->getMap()['GET'];

        $this->assertSame('ControllerOne', $map['/restful-one']['controller']);
        $this->assertSame('ControllerTwo', $map['/restful-two']['controller']);
    }

    public function testMApRegistersToClosure()
    {
        $route = new Map;

        $route->get('/', function () {
            return true;
        });

        $route->get('/two', function () {
            return true;
        });

        $map = $route->getMap()['GET'];

        $this->assertTrue($map['/']['controller'] instanceof Closure);
        $this->assertTrue($map['/two']['controller'] instanceof Closure);
    }
}
