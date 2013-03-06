<?php namespace Orno\Mvc\Route;

use Orno\Di\ContainerAwareTrait;
use Orno\Mvc\Route\Route;
use Closure;

class RouteCollection
{
    /**
     * Access the container
     */
    use ContainerAwareTrait;

    /**
     * @var array
     */
    protected $routes = [];

    /**
     * Return array of Route objects
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Add a route to the routes collection
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @param  string         $method
     * @return void
     */
    public function add($route, $destination, $method = null)
    {
        $closure = false;

        if (is_string($destination)) {
            $destination = explode('@', $destination);
            $controller  = $destination[0];

            if (! $this->getContainer()->registered($destination[0])) {
                $this->getContainer()->register($destination[0], null, true);
            }
        }

        if ($destination instanceof Closure) {
            $controller = $route;
            $closure    = true;

            $this->getContainer()->register($route, $destination, true);
        }

        $action = (is_array($destination)) ? $destination[1] : null;

        $routes[] = new Route($route, $controller, $action, $method, $closure);
    }

    /**
     * Proxy to add method for GET routes
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @return void
     */
    public function get($route, $destination)
    {
        $this->add($route, $destination, 'GET');
    }

    /**
     * Proxy to add method for POST routes
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @return void
     */
    public function post($route, $destination)
    {
        $this->add($route, $destination, 'POST');
    }

    /**
     * Proxy to add method for PUT routes
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @return void
     */
    public function put($route, $destination)
    {
        $this->add($route, $destination, 'PUT');
    }

    /**
     * Proxy to add method for PATCH routes
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @return void
     */
    public function patch($route, $destination)
    {
        $this->add($route, $destination, 'PATCH');
    }

    /**
     * Proxy to add method for DELETE routes
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @return void
     */
    public function delete($route, $destination)
    {
        $this->add($route, $destination, 'DELETE');
    }

    /**
     * Proxy to add method for OPTIONS routes
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @return void
     */
    public function options($route, $destination)
    {
        $this->add($route, $destination, 'OPTIONS');
    }
}
