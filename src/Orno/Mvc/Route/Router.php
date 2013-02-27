<?php namespace Orno\Mvc\Route;

use Orno\Di\ContainerAwareTrait;
use Orno\Mvc\Route\Map as RouteMap;
use Orno\Mvc\Controller\RestfulControllerInterface;

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
     * @param Orno\Router\RouteMap $map
     * @param string               $path
     * @param string               $method
     * @param string               $queryString
     */
    public function __construct(
        RouteMap $map = null,
        $path         = null,
        $method       = null,
        $queryString  = null
    ) {
        $this->map         = $map;
        $this->path        = $path;
        $this->method      = $method;
        $this->queryString = $queryString;
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

    public function dispatch()
    {

    }

    /**
     * Find a matching route and set the controller and action members
     *
     * @return boolean
     */
    public function match()
    {
        if (is_null($this->path)) {
            throw new \RuntimeException('Environment must be set before matching a route');
        }

        // get the map of routes for this method type
        $map = $this->map->getMap()[$this->method];

        // is there a literal match?
        if (array_key_exists($this->path, $map)) {
            $this->controller = $map[$this->path]['controller'];

            if (array_key_exists('action', $map[$this->path])) {
                $this->action = $map[$this->path]['action'];
            }

            return true;
        }

        // loop through routes to find a match
        foreach ($map as $key => $val) {
            $key = preg_replace('/\s*\([^)]*\)/', '[^/]+', $key);

            // Does the regex match?
            if (preg_match('#^' . $key . '$#', $this->path)) {
                $this->controller = $val['controller'];

                if (array_key_exists('action', $val)) {
                    $this->action = $val['action'];
                }

                return true;
            }
        }

        // if we've got this far then return false for no match
        return false;
    }
}
