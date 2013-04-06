<?php namespace Orno\Mvc\View;

use Orno\Mvc\View\AbstractRenderer;
use Symfony\Component\HttpFoundation\Response;

class Renderer extends AbstractRenderer
{
    /**
     * Render the template object and any data
     *
     * @return Symfony\Component\HttpFoundation\Response
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
