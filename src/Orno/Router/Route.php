<?php namespace Orno\Router;

use Orno\Di\ContainerAwareTrait;
use Orno\Router\RouteMap;

class Route
{
    /**
     * Get access to the container object
     */
    use ContainerAwareTrait;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $queryString;

    /**
     * @var string
     */
    protected $controller;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var Orno\Router\RouteMap
     */
    protected $map;

    /**
     * Constructor
     *
     * @param string $path
     * @param string $method
     * @param string $queryString
     * @param string $controller
     * @param string $action
     */
    public function __construct(
        RouteMap $map = null,
        $path         = null,
        $method       = null,
        $queryString  = null,
        $controller   = null,
        $action       = null
    ) {
        $this->path        = $path;
        $this->method      = $method;
        $this->queryString = $queryString;
        $this->controller  = $controller;
        $this->action      = $action;
    }

    /**
     * Set this route path
     *
     * @param  string $path
     * @return Orno\Router\Route
     */
    public function setPath($path = null)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get the path for this route
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the method for this route
     *
     * @param  string $method
     * @return Orno\Router\Route
     */
    public function setMethod($method = null)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Get the method for this request
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the route map opject
     *
     * @param  Orno\Router\RouteMap $map
     * @return Orno\Router\Route
     */
    public function setRouteMap(RouteMap $map)
    {
        $this->map = $routeMap;
        return $this;
    }

    /**
     * Parse the route from the $_SERVER environment array
     *
     * @param  array $environment
     * @return Orno\Router\Route
     */
    public function fromEnvironment($environment)
    {
        $this->path = (isset($environment['PATH_INFO']))
                    ? $environment['PATH_INFO']
                    : str_replace($environment['SCRIPT_NAME'], null, $environment['REQUEST_URI']);

        $this->method = $environment['REQUEST_METHOD'];

        if (isset($environment['QUERY_STRING'])) {
            $this->queryString = $environment['QUERY_STRING'];
        }

        return $this;
    }
}
