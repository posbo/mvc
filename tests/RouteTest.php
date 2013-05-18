<?php namespace OrnoTest;

use Orno\Mvc\Route\Route;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    public function testIsRegexMatch()
    {
        $r = new Route('/test/(required)/(?optional)', 'Namespace\Controller', 'action', 'get');

        $this->assertTrue($r->isRegexMatch('/test/segment'));
        $this->assertTrue($r->isRegexMatch('/test/segment/another-segment'));
        $this->assertFalse($r->isRegexMatch('/test'));
        $this->assertFalse($r->isRegexMatch('/test/segment/another-argument/test'));

        $this->assertTrue($r->isMethodMatch('get'));
        $this->assertFalse($r->isMethodMatch('post'));

        $this->assertSame('Namespace\Controller', $r->getController());
        $this->assertSame('action', $r->getAction());

        $this->assertFalse($r->isClosure());
    }

    public function testIsComplexRegexMatch()
    {
        $r = new Route('/test/(required)/segment/(?optional)', 'Namespace\Controller', 'action', 'post');

        $r->setScheme('https');

        $this->assertTrue($r->isRegexMatch('/test/segment/segment'));
        $this->assertTrue($r->isRegexMatch('/test/segment/segment/optional'));
        $this->assertFalse($r->isRegexMatch('/test/segment'));
        $this->assertFalse($r->isRegexMatch('/test/segment/segment/optional/extra'));

        $this->assertTrue($r->isMethodMatch('post'));
        $this->assertFalse($r->isMethodMatch('get'));

        $this->assertTrue($r->isSchemeMatch('https'));
        $this->assertFalse($r->isSchemeMatch('http'));

        $this->assertSame('Namespace', $r->getModule());
    }

    public function testIndexedArguments()
    {
        $r = new Route('/test/(required)/(?optional)', 'Namespace\Controller', 'action', 'get');

        $this->assertTrue($r->isRegexMatch('/test/segment/another-segment'));
        $this->assertSame($r->getArguments(), ['segment', 'another-segment']);
    }

    public function testNamedArguments()
    {
        $required1 = $this->getMockBuilder('ReflectionParameter')
                          ->disableOriginalConstructor()
                          ->getMock();

        $required2 = $this->getMockBuilder('ReflectionParameter')
                          ->disableOriginalConstructor()
                          ->getMock();

        $optional = $this->getMockBuilder('ReflectionParameter')
                         ->disableOriginalConstructor()
                         ->getMock();

        $required1->expects($this->any())
                 ->method('getName')
                 ->will($this->returnValue('required1'));

        $required2->expects($this->any())
                 ->method('getName')
                 ->will($this->returnValue('required2'));

        $optional->expects($this->any())
                 ->method('getName')
                 ->will($this->returnValue('optional'));

        $optional->expects($this->any())
                 ->method('isOptional')
                 ->will($this->returnValue(true));

        $optional->expects($this->any())
                 ->method('getDefaultValue')
                 ->will($this->returnValue(null));

        $reflectionMethod = $this->getMockBuilder('ReflectionMethod')
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $reflectionMethod->expects($this->once())
                         ->method('getParameters')
                         ->will($this->returnValue([$required2, $required1, $optional]));

        $r = new Route('/test/(required1)/(required2)/(?optional)', 'Namespace\Controller', 'action', 'get');

        $r->isRegexMatch('/test/some-segment1/some-segment2');

        $this->assertSame($r->getArguments($reflectionMethod), ['some-segment2', 'some-segment1', null]);
    }

    public function testDispatchReturnsResponse()
    {
        $response = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
                         ->disableOriginalConstructor()
                         ->getMock();

        $controller = $this->getMock('Controller', ['action']);

        $controller->expects($this->once())
                   ->method('action')
                   ->will($this->returnValue($response));

        $container = $this->getMock('Orno\Di\Container');

        $container->expects($this->once())
                  ->method('resolve')
                  ->will($this->returnValue($controller));

        $r = new Route('/test/(required1)/(required2)/(?optional)', 'Controller', 'action', 'get');

        $r->isRegexMatch('/test/some-segment1/some-segment2');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $r->dispatch());
    }

    public function testDispatchCreatesResponse()
    {
        $controller = $this->getMock('Controller', ['action']);

        $controller->expects($this->once())
                   ->method('action')
                   ->will($this->returnValue('Hello World!'));

        $container = $this->getMock('Orno\Di\Container');

        $container->expects($this->once())
                  ->method('resolve')
                  ->will($this->returnValue($controller));

        $r = new Route('/test/(required1)/(required2)/(?optional)', 'Controller', 'action', 'get');

        $r->isRegexMatch('/test/some-segment1/some-segment2');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $r->dispatch());
    }
}
