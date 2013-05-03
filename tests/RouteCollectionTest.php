<?php namespace OrnoTest;

use Orno\Mvc\Route\RouteCollection;
use Orno\Mvc\Route\Route;

class RouteCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testRouteCollectionAcceptsConfig()
    {
        $routes = [
            'routes' => [
                'get' => [
                    '/test/route'  => 'TestController::testAction',
                    '/test/route2' => function () {
                        return true;
                    }
                ],
                'post' => [
                    '/test/route'  => 'TestController::testAction',
                    '/test/route2' => function () {
                        return true;
                    }
                ]
            ]
        ];

        $collection = new RouteCollection($routes);

        foreach ($collection->getRoutes()['GET'] as $route) {
            $this->assertTrue($route instanceof Route);
        }

        foreach ($collection->getRoutes()['POST'] as $route) {
            $this->assertTrue($route instanceof Route);
        }
    }

    public function testAddRouteWithControllerAndAction()
    {
        $container = $this->getMock('Orno\Di\Container');

        $container->expects($this->any())
                  ->method('registered')
                  ->will($this->returnValue(true));

        $container->expects($this->any())
                  ->method('register');

        $route = new RouteCollection;

        $route->setContainer($container);

        $route->add('/test/route', 'Controller::action');
        $this->assertTrue($route->getContainer()->registered('Controller'));
    }

    public function testAddRouteWithClosure()
    {
        $container = $this->getMock('Orno\Di\Container');

        $container->expects($this->any())
                  ->method('registered')
                  ->will($this->returnValue(true));

        $container->expects($this->any())
                  ->method('register');

        $route = new RouteCollection;

        $route->setContainer($container);

        $route->add(
            '/test/route',
            function () {
                return true;
            }
        );

        $this->assertTrue($route->getContainer()->registered('/test/route'));
    }

    public function testProxyMethodsRegisterCorrectly()
    {
        $route = new RouteCollection;

        $route->add('/any/route', 'Controller::anyAction');
        $route->get('/get/route', 'Controller::getAction');
        $route->post('/post/route', 'Controller::postAction');
        $route->put('/put/route', 'Controller::putAction');
        $route->patch('/patch/route', 'Controller::patchAction');
        $route->delete('/delete/route', 'Controller::deleteAction');
        $route->options('/options/route', 'Controller::optionsAction');

        $this->assertSame(count($route->getRoutes()), 7);

        foreach ($route->getRoutes() as $method) {
            foreach ($method as $route) {
                $this->assertTrue($route instanceof Route);
            }
        }
    }

    public function testRestfulRouteCreatesAllRoutes()
    {
        $route = new RouteCollection;

        $route->restful('/restful', 'RestfulController');

        foreach ($route->getRoutes() as $method) {
            foreach ($method as $route) {
                $this->assertTrue($route instanceof Route);
            }
        }
    }
}
