<?php namespace Orno\Tests;

use PHPUnit_Framework_Testcase;
use Orno\Mvc\Route\RouteCollection;
use Orno\Mvc\Route\Dispatcher;

class DispatcherTest extends PHPUnit_Framework_Testcase
{
    public function testBeforeAndAfterHooks()
    {
        $route = new RouteCollection;

        $route->before('/test', function () {
            return 'before';
        });

        $route->add('/test', function () {
            return 'controller';
        });

        $route->after('/test', function () {
            return 'after';
        });

        $dispatch = new Dispatcher($route);
        $dispatch->override('/test', 'GET');

        $this->assertTrue($dispatch->match('ANY', 'before'));
        $this->assertTrue($dispatch->match('ANY'));
        $this->assertTrue($dispatch->match('ANY', 'after'));
    }

    public function testBeforeAndAfterHooksWithWildcards() {
        $route = new RouteCollection;

        $route->before('/test/(:catchall)', function () {
            return 'before';
        });

        $route->add('/test/(id)/(name)', function () {
            return 'controller';
        });

        $route->after('/test/(:catch-all)', function () {
            return 'after';
        });

        $dispatch = new Dispatcher($route);
        $dispatch->override('/test/id/name', 'GET');

        $this->assertTrue($dispatch->match('ANY', 'before'));
        $this->assertTrue($dispatch->match('ANY'));
        $this->assertTrue($dispatch->match('ANY', 'after'));
    }

    public function testMatchDoesNotExist()
    {
        $route = new RouteCollection;

        $route->get('/test', 'TestController::testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->override('/', 'GET');

        $this->assertFalse($dispatch->match('GET'));
    }

    public function testMatchOnLiteral()
    {
        $route = new RouteCollection;

        $route->get('/', 'TestController::testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->override('/', 'GET');

        $this->assertTrue($dispatch->match('GET'));
    }

    public function testMatchOnRequiredSegment()
    {
        $route = new RouteCollection;

        $route->get('/test/(required)', 'TestController::testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->override('/test/somesegment', 'get');

        $this->assertTrue($dispatch->match('GET'));
    }

    public function testMatchOnMultipleRequiredSegments()
    {
        $route = new RouteCollection;

        $route->get('/test/(required)/(required2)', 'TestController::testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->override('/test/somesegment/somesegment2', 'GET');

        $this->assertTrue($dispatch->match('GET'));
    }

    public function testMatchOnRequiredAndPresentOptionalSegment()
    {
        $route = new RouteCollection;

        $route->get('/test/(required)/(?optional)', 'TestController::testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->override('/test/somesegment/somesegment2', 'GET');

        $this->assertTrue($dispatch->match('GET'));
    }

    public function testMatchOnRequiredAndMissingOptionalSegment()
    {
        $route = new RouteCollection;

        $route->get('/test/(required)/(?optional)', 'TestController::testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->override('/test/somesegment', 'GET');

        $this->assertTrue($dispatch->match('GET'));
    }

    public function testMatchOnOptionalSegment()
    {
        $route = new RouteCollection;

        $route->get('/test/(?optional)', 'TestController::testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->override('/test/somesegment', 'GET');

        $this->assertTrue($dispatch->match('GET'));
    }

    public function testMatchOnOptionalMissingSegment()
    {
        $route = new RouteCollection;

        $route->get('/test/(?optional)', 'TestController::testAction');

        $dispatch = new Dispatcher($route);
        $dispatch->override('/test', 'GET');

        $this->assertTrue($dispatch->match('GET'));
    }

    public function testDispatchesClosure()
    {
        $route = new RouteCollection;

        $route->add('/', function () {
            return 'Hello World';
        });

        $dispatch = new Dispatcher($route);
        $dispatch->override('/', 'GET');

        ob_start();
        $dispatch->run();
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertSame($result, 'Hello World');
    }

    public function testDispatchesControllerAction()
    {
        $route = new RouteCollection;

        $route->before('/', 'Assets\OrnoTest\Controller::before');
        $route->add('/', 'Assets\OrnoTest\Controller::index');
        $route->after('/', 'Assets\OrnoTest\Controller::index', 'GET');

        $dispatch = new Dispatcher($route);
        $dispatch->override('/', 'GET');

        ob_start();
        $dispatch->run();
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertSame($result, 'Hello World');
    }

    public function testArgumentsPassedToAction()
    {
        $route = new RouteCollection;

        $route->before('/test/(:catchall)', function () {
            return true;
        }, 'POST');

        $route->post('/test/(argument)', function ($argument) {
            return $argument;
        });

        $route->after('/test/(:catchall)', function () {
            return true;
        }, 'POST');

        $dispatch = new Dispatcher($route);
        $dispatch->override('/test/hello', 'POST');

        ob_start();
        $dispatch->run();
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertSame($result, 'hello');
    }
}
