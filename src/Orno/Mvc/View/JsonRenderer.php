<?php namespace Orno\Mvc\View;

use Orno\Mvc\View\AbstractRenderer;
use Symfony\Component\HttpFoundation\Response;

class JsonRenderer extends AbstractRenderer
{
    /**
     * Render the data array as a json string
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function render($layout = null)
    {
        return new Response(json_encode($this->data), 200, ['content-type' => 'application/json']);
    }
}
