<?php namespace Orno\Mvc\View;

use Orno\Mvc\View\AbstractRenderer;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Response;

class XmlRenderer extends AbstractRenderer
{
    /**
     * Render the data array as xml
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function render($layout = null)
    {
        $xml = new SimpleXMLElement('<root/>');

        $this->arrayToXml($this->data, $xml);

        return new Response($xml->asXml(), 200, ['content-type' => 'text/xml']);
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
}
