<?php
/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 */
namespace Orno\Mvc\Route;

use Orno\Di\ContainerAwareTrait;
use Orno\Mvc\Route\Route;
use Closure;

/**
 * Route Collection
 *
 * A collection of route and hook event objects
 */
class RouteCollection
{
    /**
     * Access the container
     */
    use ContainerAwareTrait;

    /**
     * An array of route objects
     *
     * @var array
     */
    protected $routes = [
        'GET'     => [],
        'POST'    => [],
        'PUT'     => [],
        'PATCH'   => [],
        'DELETE'  => [],
        'OPTIONS' => []
    ];

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (isset($config['routes'])) {
            $this->setRoutes($config['routes']);
        }
    }

    /**
     * Set Routes
     *
     * Build routes array from provided config array
     *
     * @param  array $routes
     * @return void
     */
    public function setRoutes(array $routes = [])
    {
        foreach ($routes as $route => $value) {
            // simple route -> destination
            if (is_string($value) || $value instanceof \Closure) {
                $this->get($route, $value);
                continue;
            }

            // multiple method types
            foreach ($value as $method => $destination) {
                $method = strtolower($method);
                $this->{$method}($route, $destination);
            }
        }
    }

    /**
     * Get Routes
     *
     * Return array of Route objects
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Add
     *
     * Add a route to the routes collection
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @param  string         $method
     * @return void
     */
    public function add($route, $destination, $method = 'get')
    {
        $route = '/' . trim($route, '/');
        $method = strtoupper($method);
        $closure = false;

        if (is_string($destination)) {
            $destination = explode('::', $destination);
            $controller  = $destination[0];

            if (! $this->getContainer()->registered($destination[0])) {
                $this->getContainer()->register($destination[0], null, true, true);
            }
        }

        if ($destination instanceof Closure) {
            $controller = $route;
            $closure    = true;

            $this->getContainer()->register($controller, $destination);
        }

        $action = (is_array($destination)) ? $destination[1] : null;

        $this->routes[$method][] = new Route($route, $controller, $action, $method, $closure);
    }

    /**
     * Get
     *
     * Proxy to add method for GET routes
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @return void
     */
    public function get($route, $destination)
    {
        $this->add($route, $destination, 'get');
    }

    /**
     * Post
     *
     * Proxy to add method for POST routes
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @return void
     */
    public function post($route, $destination)
    {
        $this->add($route, $destination, 'post');
    }

    /**
     * Put
     *
     * Proxy to add method for PUT routes
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @return void
     */
    public function put($route, $destination)
    {
        $this->add($route, $destination, 'put');
    }

    /**
     * Patch
     *
     * Proxy to add method for PATCH routes
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @return void
     */
    public function patch($route, $destination)
    {
        $this->add($route, $destination, 'patch');
    }

    /**
     * Delete
     *
     * Proxy to add method for DELETE routes
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @return void
     */
    public function delete($route, $destination)
    {
        $this->add($route, $destination, 'delete');
    }

    /**
     * Options
     *
     * Proxy to add method for OPTIONS routes
     *
     * @param  string         $route
     * @param  string|closure $destination
     * @return void
     */
    public function options($route, $destination)
    {
        $this->add($route, $destination, 'options');
    }

    /**
     * Restful
     *
     * Creates all Route objects for a restful route
     *
     * @param  string $route
     * @param  string $destination
     * @return void
     */
    public function restful($route, $destination)
    {
        $resource = rtrim($route, '/') . '/(id)';

        $this->get($route, $destination . '::getAll');
        $this->get($resource, $destination . '::get');
        $this->post($route, $destination . '::create');
        $this->put($resource, $destination . '::update');
        $this->patch($resource, $destination . '::update');
        $this->delete($resource, $destination . '::delete');
        $this->options($route, $destination . '::options');
    }
}
