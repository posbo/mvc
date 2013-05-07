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
    protected $segments = [];

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
     * The module that the controller resides in
     *
     * @var string
     */
    protected $module;

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
        $this->method     = strtoupper($method);
        $this->closure    = $closure;
        $this->route      = $this->regexify($route);

        $this->setUriSegments($route);
    }

    /**
     * Regiexify
     *
     * Transform route into a matchable regular expresion
     *
     * @param  string $route
     * @return string
     */
    public function regexify($route)
    {
        $patterns = ['/\/\((\?.*?)\)/', '/\([^\?].*?\)/'];
        $replacements = ['(.+?)?', '(.+?)'];

        $route = preg_replace($patterns, $replacements, $route);
        return str_replace('(.+?)?', '(\\/.+?)?', $route);
    }

    /**
     * Set URI Segments
     *
     * Explodes the route path in to segments
     *
     * @param  string $route
     * @return void
     */
    public function setUriSegments($route)
    {
        $this->segments = explode('/', trim($route, '/'));
    }

    /**
     * Get URI Segments
     *
     * Return the segments array
     *
     * @return array
     */
    public function getUriSegments()
    {
        if (empty($this->segments)) {
            $this->setUriSegments($this->route);
        }

        return $this->segments;
    }

    /**
     * Set Module
     *
     * Use the first level of the controller namespace as the module
     *
     * @param  string $controller
     * @return void
     */
    public function setModule($controller)
    {
        if (strpos(trim($controller, '\\'), '\\') !== false) {
            $this->module = explode('\\', trim($controller, '\\'))[0];
        }
    }

    /**
     * Get Module
     *
     * Return the module that the controller resides in
     *
     * @return string
     */
    public function getModule()
    {
        if (is_null($this->module)) {
            $this->setModule($this->controller);
        }

        return $this->module;
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
