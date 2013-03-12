<?php namespace Orno\Tests;

use PHPUnit_Framework_Testcase;
use Orno\Mvc\View\JsonRenderer;
use Orno\Mvc\View\XmlRenderer;
use Orno\Mvc\View\PhpRenderer;
use SimpleXMLElement;
use stdClass;

class ViewOutputTest extends PHPUnit_Framework_Testcase
{
    public function testJsonOutputsCorrectly()
    {
        $view = new JsonRenderer;
        $this->assertFalse($view->snippet());
        $view->data = 'hello';
        $this->assertTrue(json_decode($view->render()) instanceof stdClass);
    }

    public function testXmlOutputsCorrectly()
    {
        $view = new XmlRenderer;
        $this->assertFalse($view->snippet());
        $view->data = 'hello';
        $this->assertTrue(is_string($view->render()));
    }

    public function testPhpOutputsCorrectly()
    {
        $view = new PhpRenderer;
        $view->snippet('content', __DIR__ . '/Assets/views/snippet.php');
        $view->data = 'hello';
        $this->assertTrue(is_string($view->render(__DIR__ . '/Assets/views/layout.php')));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLackOfSnippetThrowsException()
    {
        $view = new PhpRenderer;
        $view->snippet('content');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testLackOfLayoutThrowsException()
    {
        $view = new PhpRenderer;
        $view->render();
    }
}
