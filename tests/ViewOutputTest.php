<?php namespace OrnoTest;

use PHPUnit_Framework_TestCase;
use Orno\Mvc\View\JsonRenderer;
use Orno\Mvc\View\XmlRenderer;
use Orno\Mvc\View\Renderer;
use SimpleXMLElement;
use stdClass;
use Orno\Http\Response;

class ViewOutputTest extends PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testJsonOutputsCorrectly()
    {
        $view = new JsonRenderer;
        $view->data = 'hello';
        $this->assertInstanceOf('Orno\Http\ResponseInterface', $view->render());
    }

    /**
     * @runInSeparateProcess
     */
    public function testXmlOutputsCorrectly()
    {
        $view = new XmlRenderer;
        $view->data = 'hello';
        $this->assertInstanceOf('Orno\Http\ResponseInterface', $view->render());
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
        $this->assertInstanceOf('Orno\Http\ResponseInterface', $view->render());
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
        $this->assertInstanceOf('Orno\Http\ResponseInterface', $view->render());
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
        $container = $this->getMock('Orno\Di\Container');

        $container->expects($this->once())
                  ->method('resolve')
                  ->with($this->equalTo('testHelper'), $this->equalTo(['Hello ', 'World']))
                  ->will($this->returnValue('Hello World'));

        $view = new Renderer;

        $view->setContainer($container);

        $this->assertSame($view->testHelper('Hello ', 'World'), 'Hello World');
    }
}
