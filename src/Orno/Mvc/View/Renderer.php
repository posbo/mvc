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

class Renderer extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     *
     * @throws \Orno\Mvc\View\Exception\LayoutNotProvidedException
     */
    public function render($layout = null)
    {
        if (! isset($this->layouts['default']) && is_null($layout)) {
            throw new Exception\LayoutNotProvidedException(
                'A layout must be provided to the View\Renderer object'
            );
        }

        $layout = (isset($this->layouts[$layout]))
                ? $this->layouts[$layout]
                : $this->layouts['default'];

        ob_start();
        include $layout;
        $output = ob_get_contents();
        ob_end_clean();

        return new Response($output, 200, ['content-type' => 'text/html']);
    }
}
