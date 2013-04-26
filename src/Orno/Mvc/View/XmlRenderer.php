<?php
/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 */
namespace Orno\Mvc\View;

use Orno\Mvc\View\AbstractRenderer;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Response;

class XmlRenderer extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render($layout = null)
    {
        $xml = new SimpleXMLElement('<root/>');

        $this->arrayToXml($this->data, $xml);

        return new Response($xml->asXml(), 200, ['content-type' => 'text/xml']);
    }

    /**
     * Array to XML
     *
     * Walk array and build SimpleXML object
     *
     * @param  array            $array
     * @param  SimpleXMLElement $node
     * @return void
     */
    public function arrayToXml($array, $node)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
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
}
