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
        $match = false;

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

            // match the before and after hooks for the found route
            if (is_null($hook)) {
                $this->match($method, 'before');
                $this->match($method, 'after');
            }

            return true;
        }

        // if we have a request method and have not matched a route, we need to
        // try to match a route bound to ANY request method
        if ($method !== 'ANY') {
            $match = $this->match('ANY', $hook);
        }

        return $match;
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

        // match the actual route
        if (! $this->match($this->method)) {
            // TODO: Custom 404 routes
            (new Response('404 - Page Not Found', 404))->send();
        }

        $arguments = $this->getArguments();

        ob_start();

        // check and call a before hook
        $this->callHook('before', $arguments);

        // run the actual route
        $object = $this->collection->getContainer()->resolve($this->route->getController(), $arguments);
        if (! $this->route->isClosure()) {
            $object = call_user_func_array([$object, $this->route->getAction()], $arguments);
        }

        // send the response to the buffer
        if ($object instanceof Response) {
            $object->send();
        } else {
            (new Response($object, 200, ['content-type' => 'text/html']))->send();
        }

        // check and call an after hook
        $this->callHook('after', $arguments);

        $finalOutput = ob_get_contents();
        ob_end_clean();

        echo $finalOutput;
    }

    public function callHook($event = null, array $arguments = [])
    {
        if (is_null($event)) {
            return;
        }

        if (! is_null($this->{$event})) {
            $return = $this->collection->getContainer()->resolve($this->{$event}->getController(), $arguments);
            if (! $this->{$event}->isClosure()) {
                call_user_func_array([$return, $this->{$event}->getAction()], $arguments);
            }
        }
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
