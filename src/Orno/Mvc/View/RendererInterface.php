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
    /**
     * Add View Path
     *
     * Append a path to the view script path stack
     *
     * @param  string|array $paths
     * @return void
     */
    public function addViewPath($paths);

    /**
     * Add Layout
     *
     * Add a layout to the Renderer object
     *
     * @param  array $layouts
     * @return void
     */
    public function addLayout(array $layouts);

    /**
     * Render
     *
     * Render a view with an optional chanbge of layout
     *
     * @param  string $layout
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($layout = null);

    /**
     * Region
     *
     * Set or write to a region
     *
     * @throws \Orno\Mvc\View\Exception\RegionNotProvidedException
     * @throws \Orno\Mvc\View\Exception\ViewPathNotProvidedException
     * @param  string  $region
     * @param  string  $content
     * @param  array   $data
     * @return void
     */
    public function region($region = null, $content = null, array $data = []);

    /**
     * Magic call method for view helpers
     *
     * @throws \Orno\Mvc\View\Exception\HelperNotFoundException
     * @param  string $helper
     * @param  array  $args
     * @return mixed
     */
    public function __call($helper, $args);
}
