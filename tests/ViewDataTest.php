<?php namespace Orno\Tests;

use PHPUnit_Framework_Testcase;
use Orno\Mvc\View\JsonRenderer;
use Orno\Mvc\View\XmlRenderer;
use SimpleXMLElement;

class ViewDataTest extends PHPUnit_Framework_Testcase
{
    public function testMagicMethodsSetData()
    {
        $json = new JsonRenderer;
        $xml  = new XmlRenderer;

        $json->data = 'json';
        $xml->data = 'xml';

        $this->assertTrue(isset($json->data));
        $this->assertTrue(isset($xml->data));
        $this->assertSame($json->data, 'json');
        $this->assertSame($xml->data, 'xml');

        unset($json->data);
        unset($xml->data);

        $this->assertFalse(isset($json->data));
        $this->assertFalse(isset($xml->data));
    }

    public function testArrayAccessSetData()
    {
        $json = new JsonRenderer;
        $xml  = new XmlRenderer;

        $json['data'] = 'json';
        $xml['data'] = 'xml';

        $this->assertTrue(isset($json['data']));
        $this->assertTrue(isset($xml['data']));
        $this->assertSame($json['data'], 'json');
        $this->assertSame($xml['data'], 'xml');

        unset($json['data']);
        unset($xml['data']);

        $this->assertFalse(isset($json['data']));
        $this->assertFalse(isset($xml['data']));
    }

    public function testArrayToXmlConversion()
    {
        $xml = new SimpleXMLElement('<root/>');
        $array = ['data' => [['test' => 'alpha', 3 => 'numerical'],['test' => 'alpha', 3 => 'numerical']], 'test' => 'named key'];

        $xmlView = new XmlRenderer;

        $xmlView->arrayToXml($array, $xml);

        $this->assertTrue($xml instanceof SimpleXMLElement);
    }
}
