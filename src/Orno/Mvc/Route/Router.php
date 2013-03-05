<?php namespace Orno\Mvc\Route;

use Orno\Di\ContainerAwareTrait;
use Orno\Mvc\Route\Map as RouteMap;
use Orno\Mvc\Controller\RestfulControllerInterface;

class Router
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
    protected $matched;

    /**
     * @var string
     */
    protected $method;

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
    protected $arguments;

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
     */
    public function __construct(
        RouteMap $map = null,
        $path         = null,
        $method       = null
    ) {
        $this->map         = $map;
        $this->path        = $path;
        $this->method      = $method;
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
     * Return the matched route
     *
     * @return string
     */
    public function getMatched()
    {
        return $this->matched;
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
        $map = $this->map->getMap();

        if (! $this->match()) {
            // has a 404 route been defined in the map? if not, we'll trigger the
            // minimum 404 response and bail out
            if (! array_key_exists('404', $map)) {
                header('HTTP/1.0 404 Not Found');
                die('Error 404 - Page not found');
            }

            // recommended to set a 404 route in the route map for showing a custom 404 page
            $this->controller = $map['404']['controller'];
        }
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
        $map = $this->map->getMap();

        // is there a literal match?
        if (array_key_exists($this->path, $map)) {
            $this->controller = $map[$this->path]['controller'];

            if (array_key_exists('action', $map[$this->path])) {
                $this->action = $map[$this->path]['action'];
            }

            $this->matched = $this->path;

            return true;
        }

        // loop through routes to find a match
        foreach ($map as $key => $val) {
            $candidate = $key;
            $key = preg_replace('/\s*\([^)]*\)/', '[^/]+', $key);

            // Does the regex match?
            if (preg_match('#^' . $key . '$#', $this->path)) {
                $this->controller = $val['controller'];

                if (array_key_exists('action', $val)) {
                    $this->action = $val['action'];
                }

                $this->matched = $candidate;

                return true;
            }
        }

        // if we've got this far then return false for no match
        return false;
    }

    /**
     * Set the params to pass to the action
     *
     * @return void
     */
    public function setArguments()
    {

    }
}
