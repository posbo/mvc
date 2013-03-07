<?php namespace Orno\Mvc\Route;

use Orno\Mvc\Route\RouteCollection;

class Dispatcher
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $segments;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var Orno\Mvc\Route\Route
     */
    protected $route;

    /**
     * Constructor
     *
     * @param Orno\Mvc\Route\RouteCollection $collection
     */
    public function __construct(RouteCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Explode the route path in to segments
     *
     * @return void
     */
    public function setSegments()
    {
        if (! is_null($this->path)) {
            $this->segments = explode('/', trim($this->path, '/'));
        }
    }

    /**
     * Set the route environment from the $_SERVER array or mock
     *
     * @param  array                     $server
     * @return Orno\Mvc\Route\Dispatcher $this
     */
    public function setEnvironment(array $server = [])
    {
        $route = str_replace($server['SCRIPT_NAME'], null, $server['REQUEST_URI']);

        $this->path   = ($route === '') ? '/' : $route;
        $this->method = $server['REQUEST_METHOD'];

        $this->setSegments();

        return $this;
    }

    public function match($method = 'ALL')
    {
        foreach ($this->collection->getRoutes()[$method] as $route) {
            // is there a literal match?
            if ($route->getRoute() === $this->path) {
                $this->route = $route;
                return true;
            }

            if (! preg_match('#^' . $route->getRoute() . '$#', $this->path)) {
                continue;
            }

            $this->route = $route;
            return true;
        }

        return false;
    }
}
