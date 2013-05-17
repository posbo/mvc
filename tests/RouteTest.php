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
}
