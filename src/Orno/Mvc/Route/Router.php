<?php namespace Orno\Mvc\Route;

use Orno\Di\ContainerAwareTrait;
use Orno\Mvc\Route\Route;

class Router
{
    /**
     * Access the container
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
     * @var array
     */
    protected $routes = [];

    /**
     * Constructor
     *
     * @param string $path
     * @param string $method
     */
    public function __construct($path = null, $method = null)
    {
        $this->path   = $path;
        $this->method = $method;
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
     * @param  string $route
     * @param  string $destination
     * @return void
     */
    public function get($route, $destination)
    {
        $this->add($route, $destination, 'GET');
    }

    /**
     * Proxy to add method for POST routes
     *
     * @param  string $route
     * @param  string $destination
     * @return void
     */
    public function post($route, $destination)
    {
        $this->add($route, $destination, 'POST');
    }

    /**
     * Proxy to add method for PUT routes
     *
     * @param  string $route
     * @param  string $destination
     * @return void
     */
    public function put($route, $destination)
    {
        $this->add($route, $destination, 'PUT');
    }

    /**
     * Proxy to add method for DELETE routes
     *
     * @param  string $route
     * @param  string $destination
     * @return void
     */
    public function delete($route, $destination)
    {
        $this->add($route, $destination, 'DELETE');
    }
}
