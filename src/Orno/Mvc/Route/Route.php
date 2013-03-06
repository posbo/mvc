<?php namespace Orno\Mvc\Route;

use Orno\Mvc\Controller\RestfulControllerInterface;

class Route
{
    /**
     * @var string
     */
    protected $route;

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
     * @var array
     */
    protected $params;

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
        $route        = null,
        $controller   = null,
        $action       = null,
        $method       = null,
        $closure      = false
    ) {
        $this->route      = $route;
        $this->controller = $controller;
        $this->action     = $action;
        $this->method     = $method;
        $this->closure    = $closure;
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
     * Set the params array
     *
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Return the params array
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
