<?php namespace Orno\Mvc\Route;

use Orno\Mvc\Route\RouteCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class Dispatcher
{
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
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $method;

    /**
     * Constructor
     *
     * @param Orno\Mvc\Route\RouteCollection $collection
     */
    public function __construct(RouteCollection $collection, $path = null, $method = null)
    {
        $this->collection = $collection;
        $this->request = Request::createFromGlobals();
        $this->path = (is_null($path)) ? $this->request->getPathInfo() : $path;
        $this->method = (is_null($method)) ? $this->request->getMethod() : strtoupper($method);
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
        // match the actual route
        if (! $this->match($this->method)) {
            // TODO: Custom 404 routes
            (new Response('404 - Page Not Found', 404))->send();
            return false;
        }

        // match any hooks
        $this->match($this->method, 'before');
        $this->match($this->method, 'after');

        $arguments = $this->getArguments();

        ob_start();

        // check and call a before hook
        $this->trigger('before', $arguments);

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
        $this->trigger('after', $arguments);

        $output = ob_get_contents();
        ob_end_clean();

        echo $output;
    }

    public function trigger($event = null, array $arguments = [])
    {
        if (is_null($event)) {
            return;
        }

        if (! is_null($this->{$event})) {
            $object = $this->collection->getContainer()->resolve($this->{$event}->getController(), $arguments);
            if (! $this->{$event}->isClosure()) {
                call_user_func_array([$object, $this->{$event}->getAction()], $arguments);
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
