<?php namespace Orno\Mvc\View;

use ArrayAccess;
use Orno\Mvc\View\RendererInterface;
use SimpleXMLElement;

class XmlRenderer implements ArrayAccess, RendererInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Only used in Orno\Mvc\View\PhpRenderer
     *
     * @return boolean
     */
    public function snippet($key = null, $path = null)
    {
        return false;
    }

    /**
     * Render the data array as xml
     *
     * @return string
     */
    public function render($layout = null)
    {
        $xml = new SimpleXMLElement('<root/>');

        $this->arrayToXml($this->data, $xml);

        header('Content-Type: text/xml');
        echo $xml->asXml();
    }

    /**
     * Walk array and build SimpleXML object
     *
     * @param  array            $array
     * @param  SimpleXMLElement $node
     * @return void
     */
    public function arrayToXml($array, $node)
    {
        foreach($array as $key => $value) {
            if(is_array($value)) {
                if (is_numeric($key)) {
                    $subnode = $node->addChild('node');
                } else {
                    $subnode = $node->addChild($key);
                }
                $this->arrayToXml($value, $subnode);
            } else {
                $node->addChild($key, $value);
            }
        }
    }

    /**
     * Set a data value
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Set a data value
     *
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Get a data value
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data[$key];
    }

    /**
     * Get a data value
     *
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    /**
     * Check if a data value is set
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Check if a data value is set
     *
     * @param  string $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Unset a data value
     *
     * @param string $key
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Unset a data value
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }
}
