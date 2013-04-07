<?php namespace Orno\Tests;

use PHPUnit_Framework_Testcase;
use Orno\Mvc\View\JsonRenderer;
use Orno\Mvc\View\XmlRenderer;
use Orno\Mvc\View\Renderer;
use SimpleXMLElement;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

class ViewOutputTest extends PHPUnit_Framework_Testcase
{
    /**
     * @runInSeparateProcess
     */
    public function testJsonOutputsCorrectly()
    {
        $view = new JsonRenderer;
        $view->data = 'hello';
        $this->assertTrue($view->render() instanceof Response);
    }

    /**
     * @runInSeparateProcess
     */
    public function testXmlOutputsCorrectly()
    {
        $view = new XmlRenderer;
        $view->data = 'hello';
        $this->assertTrue($view->render() instanceof Response);
    }

    /**
     * @runInSeparateProcess
     */
    public function testPhpOutputsCorrectly()
    {
        $view = new Renderer;
        $view->addLayout(['default' => __DIR__ . '/Assets/views/layout.php']);
        $view->addViewPath(__DIR__ . '/Assets/views/');
        $view->region('content', 'snippet');
        $this->assertTrue($view->render() instanceof Response);
    }

    public function testRegionAcceptsDataArray()
    {
        $view = new Renderer;
        $data = [
            'test' => 'Hello',
            'test2' => 'World'
        ];
        $view->addViewPath(__DIR__ . '/Assets/views/');
        $view->region('content', 'snippet', $data);
        $this->assertSame($view->test, 'Hello');
        $this->assertSame($view->test2, 'World');
    }

    /**
     * @runInSeparateProcess
     */
    public function testRegionAcceptsContentString()
    {
        $view = new Renderer;
        $view->addLayout(['default' => __DIR__ . '/Assets/views/layout.php']);
        $view->addViewPath(__DIR__ . '/Assets/views/');
        $view->region('content', 'Hello World!');
        $this->assertTrue($view->render() instanceof Response);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLackOfRegionThrowsException()
    {
        $view = new Renderer;
        $view->region();
    }

    /**
     * @expectedException RuntimeException
     */
    public function testLackOfLayoutThrowsException()
    {
        $view = new Renderer;
        $view->render();
    }

    public function testResolvingHelper()
    {
        $view = new Renderer;
        $view->getContainer()->register('testHelper', function ($hello, $world) {
            return $hello . $world;
        });
        $this->assertSame($view->testHelper('Hello ', 'World'), 'Hello World');
    }
}
