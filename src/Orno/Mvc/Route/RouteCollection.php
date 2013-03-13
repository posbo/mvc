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
    protected $routes = [
        'ANY'     => [],
        'GET'     => [],
        'POST'    => [],
        'PUT'     => [],
        'PATCH'   => [],
        'DELETE'  => [],
        'OPTIONS' => []
    ];

    protected $hooks = [
        'before' => [
            'ANY'     => [],
            'GET'     => [],
            'POST'    => [],
            'PUT'     => [],
            'PATCH'   => [],
            'DELETE'  => [],
            'OPTIONS' => []
        ],
        'after'  => [
            'ANY'     => [],
            'GET'     => [],
            'POST'    => [],
            'PUT'     => [],
            'PATCH'   => [],
            'DELETE'  => [],
            'OPTIONS' => []
        ]
    ];

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (! empty($config)) {
            // loop through routes and register a Route object
            if (isset($config['routes'])) {
                foreach ($config['routes'] as $key => $values) {
                    foreach ($values as $value) {
                        $this->{strtolower($key)}($value[0], $value[1]);
                    }
                }
            }

            // loop through any hooks and register them in the same way as a route
            // but store them in the hooks array
            if (isset($config['hooks'])) {
                foreach ($config['hooks'] as $key => $values) {
                    foreach ($values as $value) {
                        $this->{strtolower($key)}($value[0], $value[1]);
                    }
                }
            }
        }
    }

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
     * Return array of Hooks
     *
     * @return array
     */
    public function getHooks()
    {
        return $this->hooks;
    }

    /**
     * Add a route to the routes collection
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @param  string         $method
     * @return void
     */
    public function add($route, $destination, $method = 'ANY', $hook = null)
    {
        $closure = false;

        if (is_string($destination)) {
            $destination = explode('@', $destination);
            $controller  = $destination[0];

            if (! $this->getContainer()->registered($destination[0])) {
                $this->getContainer()->register($destination[0], null);
            }
        }

        if ($destination instanceof Closure) {
            $controller = (is_null($hook)) ? $route : $hook . ':' . $route;
            $closure    = true;

            $this->getContainer()->register($controller, $destination);
        }

        $action = (is_array($destination)) ? $destination[1] : null;

        if (is_null($hook)) {
            $this->routes[$method][] = new Route($route, $controller, $action, $method, $closure);
        } else {
            $this->hooks[$hook][$method][] = new Route($route, $controller, $action, $method, $closure);
        }
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

    /**
     * Creates all Route objects for a restful route
     *
     * @param  string $route
     * @param  string $destination
     * @return void
     */
    public function restful($route, $destination)
    {
        $resource = rtrim($route, '/') . '/(?id)';

        $this->get($route, $destination . '@getAll');
        $this->get($resource, $destination . '@get');
        $this->post($route, $destination . '@create');
        $this->put($resource, $destination . '@update');
        $this->patch($resource, $destination . '@update');
        $this->delete($resource, $destination . '@delete');
        $this->options($route, $destination . '@options');
    }

    /**
     * Register a hook to be run before the controller action
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @param  string         $method
     * @return
     */
    public function before($route, $destination, $method = 'ANY')
    {
        $this->add($route, $destination, $method, 'before');
    }

    /**
     * Register a hook to be run after the controller action
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @param  string         $method
     * @return
     */
    public function after($route, $destination, $method = 'ANY')
    {
        $this->add($route, $destination, $method, 'after');
    }
}
