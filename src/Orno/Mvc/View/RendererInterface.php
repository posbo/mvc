<?php
/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 */
namespace Orno\Mvc\View;

/**
 * Renderer Interface
 */
interface RendererInterface
{
    public function addViewPath($paths);

    public function addLayout(array $layouts);

    public function render($layout = null);

    public function region($region = null, $content = null, array $data = []);

    public function __call($helper, $args);
}
