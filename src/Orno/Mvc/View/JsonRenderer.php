<?php
/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 */
namespace Orno\Mvc\View;

use Orno\Mvc\View\AbstractRenderer;
use Symfony\Component\HttpFoundation\Response;

class JsonRenderer extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render($layout = null)
    {
        return new Response(json_encode($this->data), 200, ['content-type' => 'application/json']);
    }
}
