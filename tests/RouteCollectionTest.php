<?php namespace OrnoTest;

use Orno\Mvc\Route\RouteCollection;
use Orno\Mvc\Route\Route;

class RouteCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testRouteCollectionAcceptsConfig()
    {
        $routes = [
            '/test/route' => [
                'get' => 'TestController::testAction',
                'post' => 'TestController::testPostAction'
            ],
            '/test/route2' => [
                'get' => function () { return true; },
                'post' => function () { return true; }
            ],
            '/test/route3' => 'TestController::testAction'
        ];

        $rc = new RouteCollection($routes);

        foreach ($rc->getRoutes() as $route) {
            $this->assertInstanceOf('Orno\Mvc\Route\Route', $route);
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

        $rc = new RouteCollection;

        $rc->setContainer($container);

        $rc->add('/test/route', 'Controller::action');
        $this->assertTrue($rc->getContainer()->registered('Controller'));
    }

    public function testAddRouteWithClosure()
    {
        $container = $this->getMock('Orno\Di\Container');

        $container->expects($this->any())
                  ->method('registered')
                  ->will($this->returnValue(true));

        $container->expects($this->any())
                  ->method('register');

        $rc = new RouteCollection;

        $rc->setContainer($container);

        $rc->add('/test/route', function () {
            return true;
        });

        $this->assertTrue($rc->getContainer()->registered('/test/route'));
    }

    public function testProxyMethodsRegisterCorrectly()
    {
        $rc = new RouteCollection;

        $rc->add('/any/route', 'Controller::anyAction');
        $rc->get('/get/route', 'Controller::getAction');
        $rc->post('/post/route', 'Controller::postAction');
        $rc->put('/put/route', 'Controller::putAction');
        $rc->patch('/patch/route', 'Controller::patchAction');
        $rc->delete('/delete/route', 'Controller::deleteAction');
        $rc->options('/options/route', 'Controller::optionsAction');

        $this->assertSame(count($rc->getRoutes()), 7);

        foreach ($rc->getRoutes() as $route) {
            $this->assertInstanceOf('Orno\Mvc\Route\Route', $route);
        }
    }

    public function testRestfulRouteCreatesAllRoutes()
    {
        $rc = new RouteCollection;

        $rc->restful('/restful', 'RestfulController');

        foreach ($rc->getRoutes() as $route) {
            $this->assertInstanceOf('Orno\Mvc\Route\Route', $route);
        }
    }

    public function testMatchesRoute()
    {
        $rc = new RouteCollection();

        $routes = new \ReflectionProperty($rc, 'routes');
        $routes->setAccessible(true);
        $routes->setValue($rc, $this->mockMatchableRoutesArray());

        $this->assertInstanceOf('Orno\Mvc\Route\Route', $rc->match('/'));
    }

    public function testNoMatchReturnsFalse()
    {
        $rc = new RouteCollection();

        $routes = new \ReflectionProperty($rc, 'routes');
        $routes->setAccessible(true);
        $routes->setValue($rc, $this->mockUnMatchableRoutesArray());

        $this->assertFalse($rc->match('/', 'post'));
    }

    public function mockMatchableRoutesArray()
    {
        $route1 = $this->getMock('Orno\Mvc\Route\Route');
        $route1->expects($this->any())
               ->method('isRegexMatch')
               ->will($this->returnValue(true));
        $route1->expects($this->any())
               ->method('isMethodMatch')
               ->will($this->returnValue(false));
        $route1->expects($this->any())
               ->method('isSchemeMatch')
               ->will($this->returnValue(true));

        $route2 = $this->getMock('Orno\Mvc\Route\Route');
        $route2->expects($this->any())
               ->method('isRegexMatch')
               ->will($this->returnValue(false));
        $route2->expects($this->any())
               ->method('isMethodMatch')
               ->will($this->returnValue(false));
        $route2->expects($this->any())
               ->method('isSchemeMatch')
               ->will($this->returnValue(true));

        $route3 = $this->getMock('Orno\Mvc\Route\Route');
        $route3->expects($this->any())
               ->method('isRegexMatch')
               ->will($this->returnValue(false));
        $route3->expects($this->any())
               ->method('isMethodMatch')
               ->will($this->returnValue(true));
        $route3->expects($this->any())
               ->method('isSchemeMatch')
               ->will($this->returnValue(false));

        $route4 = $this->getMock('Orno\Mvc\Route\Route');
        $route4->expects($this->any())
               ->method('isRegexMatch')
               ->will($this->returnValue(true));
        $route4->expects($this->any())
               ->method('isMethodMatch')
               ->will($this->returnValue(true));
        $route4->expects($this->any())
               ->method('isSchemeMatch')
               ->will($this->returnValue(true));

        return [
            $route1, $route2, $route3, $route4
        ];
    }

    public function mockUnMatchableRoutesArray()
    {
        $route1 = $this->getMock('Orno\Mvc\Route\Route');
        $route1->expects($this->any())
               ->method('isRegexMatch')
               ->will($this->returnValue(true));
        $route1->expects($this->any())
               ->method('isMethodMatch')
               ->will($this->returnValue(false));
        $route1->expects($this->any())
               ->method('isSchemeMatch')
               ->will($this->returnValue(true));

        $route2 = $this->getMock('Orno\Mvc\Route\Route');
        $route2->expects($this->any())
               ->method('isRegexMatch')
               ->will($this->returnValue(false));
        $route2->expects($this->any())
               ->method('isMethodMatch')
               ->will($this->returnValue(false));
        $route2->expects($this->any())
               ->method('isSchemeMatch')
               ->will($this->returnValue(true));

        $route3 = $this->getMock('Orno\Mvc\Route\Route');
        $route3->expects($this->any())
               ->method('isRegexMatch')
               ->will($this->returnValue(false));
        $route3->expects($this->any())
               ->method('isMethodMatch')
               ->will($this->returnValue(true));
        $route3->expects($this->any())
               ->method('isSchemeMatch')
               ->will($this->returnValue(false));

        $route4 = $this->getMock('Orno\Mvc\Route\Route');
        $route4->expects($this->any())
               ->method('isRegexMatch')
               ->will($this->returnValue(true));
        $route4->expects($this->any())
               ->method('isMethodMatch')
               ->will($this->returnValue(false));
        $route4->expects($this->any())
               ->method('isSchemeMatch')
               ->will($this->returnValue(true));

        return [
            $route1, $route2, $route3, $route4
        ];
    }
}
