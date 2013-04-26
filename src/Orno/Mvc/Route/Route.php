<?php
/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 */
namespace Orno\Mvc\Route;

/**
 * Route
 *
 * A single route object
 */
class Route
{
    /**
     * The route path
     *
     * @var string
     */
    protected $route;

    /**
     * Array of segments in the route path
     *
     * @var array
     */
    protected $segments;

    /**
     * Points to item registered with Orno\Di\Container
     *
     * @var string
     */
    protected $controller;

    /**
     * The name of the action to invoke
     *
     * @var string
     */
    protected $action;

    /**
     * Will assume ANY method if null
     *
     * @var string
     */
    protected $method;

    /**
     * Is the controller a closure?
     *
     * @var boolean
     */
    protected $closure;

    /**
     * Constructor
     *
     * @param string  $route
     * @param string  $controller
     * @param string  $action
     * @param string  $method
     * @param boolean $closure
     */
    public function __construct(
        $route = null,
        $controller = null,
        $action = null,
        $method = null,
        $closure = false
    ) {
        $this->controller = $controller;
        $this->action     = $action;
        $this->method     = $method;
        $this->closure    = $closure;

        $this->setSegments($route);

        $patterns = [
            '/\/\(:any\)/',
            '/\/\(:all\)/',
            '/\/\(:catchall\)/',
            '/\/\((\?.*?)\)/',
            '/\([^\/].*?\)/'
        ];

        $replacements = [
            '(\/.+)?',
            '(\/.+)?',
            '(\/.+)?',
            '(\/.+?)?',
            '(.+?)'
        ];

        $route = preg_replace($patterns, $replacements, $route);
        $this->route = $route;
    }

    /**
     * Set Segments
     *
     * Explodes the route path in to segments
     *
     * @return void
     */
    public function setSegments($route)
    {
        $this->segments = explode('/', trim($route, '/'));
    }

    /**
     * Get Segments
     *
     * Return the segments array
     *
     * @return array
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * Get Route
     *
     * Return the route path string
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Get Controller
     *
     * Return the controller name (alias registered with the container)
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get Action
     *
     * Return the action name
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get Method
     *
     * Return the HTTP method type
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Is Closure?
     *
     * @return boolean
     */
    public function isClosure()
    {
        return $this->closure;
    }
}
