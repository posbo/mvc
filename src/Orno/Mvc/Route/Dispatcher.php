<?php namespace Orno\Mvc\Route;

use Orno\Mvc\Route\RouteCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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
     * @var Orno\Mvc\Route\Route
     */
    protected $before;

    /**
     * @var Orno\Mvc\Route\Route
     */
    protected $after;

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param Orno\Mvc\Route\RouteCollection $collection
     */
    public function __construct(RouteCollection $collection) {
        $this->collection = $collection;
        $this->request = Request::createFromGlobals();

        $this->buildFromRequest();
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
     * Build the environment from the Request Object
     *
     * @return void
     */
    protected function buildFromRequest()
    {
        $this->path = $this->request->getPathInfo();
        $this->method = $this->request->getMethod();

        $this->setSegments();
    }

    /**
     * Override any route params.
     *
     * @param  string                    $path
     * @param  string                    $method
     * @return Orno\Mvc\Route\Dispatcher $this
     */
    public function override($path = null, $method = null)
    {
        if (! is_null($path)) {
            $this->path = $path;
        }

        if (! is_null($method)) {
            $this->method = strtoupper($method);
        }

        $this->setSegments();

        return $this;
    }

    /**
     * Match the path against a route
     *
     * @param  string $method
     * @return boolean
     */
    public function match($method = 'ANY', $hook = null)
    {
        $routes = (is_null($hook))
                ? $this->collection->getRoutes()[$method]
                : $this->collection->getHooks()[$hook][$method];

        foreach ($routes as $route) {
            // is there a literal match?
            if ($route->getRoute() === $this->path) {
                if (is_null($hook)) {
                    $this->route = $route;
                } else {
                    $this->{$hook} = $route;
                }

                return true;
            }

            if (! preg_match('#^' . $route->getRoute() . '$#', $this->path)) {
                continue;
            }

            if (is_null($hook)) {
                $this->route = $route;
            } else {
                $this->{$hook} = $route;
            }

            return true;
        }

        return false;
    }

    /**
     * Dispatch the route
     *
     * @return void
     */
    public function run()
    {
        if (is_null($this->path) || is_null($this->method)) {
            throw new \RuntimeException('Environment must be set before dispatching');
        }

        // match any before hooks
        if (! $this->match($this->method, 'before')) {
            $this->match('ANY', 'before');
        }

        // match any after hooks
        if (! $this->match($this->method, 'after')) {
            $this->match('ANY', 'after');
        }

        // match the actual route
        if (! $this->match($this->method)) {
            // if no route found for the method we fall back
            // to ANY method for a match
            if (! $this->match()) {
                // TODO: Custom 404 routes
                (new Response('404 - Page Not Found', 404))->send();
            }
        }



        $arguments = $this->getArguments();

        ob_start();

        // run the before hook
        if (! is_null($this->before)) {
            $before = $this->collection->getContainer()->resolve($this->before->getController(), $arguments);
            if (! $this->before->isClosure()) {
                $before = call_user_func_array([$before, $this->before->getAction()], $arguments);
            }
        }

        // run the actual route
        $object = $this->collection->getContainer()->resolve($this->route->getController(), $arguments);
        if (! $this->route->isClosure()) {
            $object = call_user_func_array([$object, $this->route->getAction()], $arguments);
        }

        // output the results to the browser
        if ($object instanceof Response) {
            $object->send();
        } else {
            echo $object;
        }

        // run the after route
        if (! is_null($this->after)) {
            $after = $this->collection->getContainer()->resolve($this->after->getController(), $arguments);
            if (! $this->after->isClosure()) {
                $after = call_user_func_array([$after, $this->after->getAction()], $arguments);
            }
        }

        $finalOutput = ob_get_contents();
        ob_end_clean();

        echo $finalOutput;
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
                if (isset($segments[$key])) {
                    $arguments[] = $segments[$key];
                } else {
                    $arguments[] = null;
                }
            }
        }

        return $arguments;
    }
}
