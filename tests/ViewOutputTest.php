<?php namespace Orno\Tests;

use PHPUnit_Framework_Testcase;
use Orno\Mvc\View\JsonRenderer;
use Orno\Mvc\View\XmlRenderer;
use Orno\Mvc\View\Renderer;
use SimpleXMLElement;
use stdClass;

class ViewOutputTest extends PHPUnit_Framework_Testcase
{
    /**
     * @runInSeparateProcess
     */
    public function testJsonOutputsCorrectly()
    {
        $view = new JsonRenderer;
        $this->assertFalse($view->region());
        $view->data = 'hello';
        $this->assertTrue(json_decode($view->render()) instanceof stdClass);
    }

    /**
     * @runInSeparateProcess
     */
    public function testXmlOutputsCorrectly()
    {
        $view = new XmlRenderer;
        $this->assertFalse($view->region());
        $view->data = 'hello';
        $this->assertTrue(is_string($view->render()));
    }

    /**
     * @runInSeparateProcess
     */
    public function testPhpOutputsCorrectly()
    {
        $view = new Renderer;
        $view->region('content', __DIR__ . '/Assets/views/snippet.php');
        $this->assertTrue(is_string($view->render(__DIR__ . '/Assets/views/layout.php')));
    }

    public function testRegionAcceptsDataArray()
    {
        $view = new Renderer;
        $data = [
            'test' => 'Hello',
            'test2' => 'World'
        ];
        $view->region('content', __DIR__ . '/Assets/views/snippet.php', $data);
        $this->assertSame($view->test, 'Hello');
        $this->assertSame($view->test2, 'World');
    }

    /**
     * @runInSeparateProcess
     */
    public function testRegionAcceptsContentString()
    {
        $view = new Renderer;
        $view->region('content', 'Hello World!');
        $this->assertTrue(is_string($view->render(__DIR__ . '/Assets/views/layout.php')));
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
}
