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
    protected $arguments = [];

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
    public function setEnvironment(array $server)
    {
        if (! empty($server)) {
            $route = str_replace($server['SCRIPT_NAME'], null, $server['REQUEST_URI']);

            $this->path   = ($route === '') ? '/' : $route;
            $this->method = $server['REQUEST_METHOD'];

            $this->setSegments();
        }

        return $this;
    }

    /**
     * Match the path against a route
     *
     * @param  string $method
     * @return boolean
     */
    public function match($method = 'GET')
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

    /**
     * Dispatch the route
     *
     * @return mixed
     */
    public function run()
    {
        if (is_null($this->path) || is_null($this->method)) {
            throw new \RuntimeException('Environment must be set before dispatching');
        }

        if (! $this->match($this->method)) {
            // TODO: handle 404
            throw new \RuntimeException('No route found for ' . $this->path);
        }

        $arguments = $this->getArguments();

        $object = $this->collection->getContainer()->resolve($this->route->getController(), $arguments);

        if (! $this->route->isClosure()) {
            $object = call_user_func_array([$object, $this->route->getAction()], $arguments);
        }

        return $object;
    }

    /**
     * Get the arguments to pass to the action
     *
     * @return array
     */
    public function getArguments()
    {
        $arguments = [];
        $segments  = explode('/', trim($this->path, '/'));

        if (! empty($segments)) {
            $keys = preg_grep('/\([^\/].*?\)/', $this->route->getSegments());
        }

        if (! empty($keys)) {
            foreach ($keys as $key => $val) {
                $arguments[] = $segments[$key];
            }
        }

        return $arguments;
    }
}
