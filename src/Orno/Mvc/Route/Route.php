<?php namespace Orno\Mvc\Route;

use Orno\Mvc\Controller\RestfulControllerInterface;

class Route
{
    /**
     * @var string
     */
    protected $route;

    /**
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
        $route      = null,
        $controller = null,
        $action     = null,
        $method     = null,
        $closure    = false
    ) {
        $this->controller = $controller;
        $this->action     = $action;
        $this->method     = $method;
        $this->closure    = $closure;

        $this->setSegments($route);

        $route = preg_replace('/\/\((\?.*?)\)/', '(\/.*)?', $route);
        $route = preg_replace('/\([^\/].*?\)/', '(.*)', $route);
        $this->route = $route;
    }

    /**
     * Set the segments for this route
     *
     * @return void
     */
    public function setSegments($route)
    {
        $this->segments = explode('/', trim($route, '/'));
    }

    /**
     * Return the segments array
     *
     * @return array
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * Return the route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Return the controller
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Return the action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Return the method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Is the controller a closure?
     *
     * @return boolean
     */
    public function isClosure()
    {
        return $this->closure;
    }
}
