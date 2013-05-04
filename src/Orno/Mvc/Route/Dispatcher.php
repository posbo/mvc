<?php
/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 */
namespace Orno\Mvc\Route;

use Orno\Di\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Dispatcher
 *
 * Accepts a route collection object as a dependency and dispatches the application
 */
class Dispatcher
{
    /**
     * Access to the container
     */
    use ContainerAwareTrait;

    /**
     * The main route object
     *
     * @var \Orno\Mvc\Route\Route
     */
    protected $route;

    /**
     * The HTTP request method
     *
     * @var string
     */
    protected $method;

    /**
     * The current path
     *
     * @var string
     */
    protected $path;

    /**
     * Constructor
     *
     * @param \Orno\Mvc\Route\RouteCollection $collection
     */
    public function __construct(RouteCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Match
     *
     * Match the path against a route
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  boolean $any - whether to check the 'ANY' request method
     * @return boolean
     */
    public function match(Request $request, $any = false, $notFound = false)
    {
        $this->path = ($notFound === false) ? $request->getPathInfo() : '/404';

        $method = ($any === true) ? 'ANY' : $request->getMethod();
        $routes = $this->collection->getRoutes()[$method];

        // loop through the routes array for a match
        foreach ($routes as $route) {
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

        // if we have a request method and have not matched a route, we need to
        // try to match a route bound to ANY request method
        if ($any === false) {
            return $this->match($request, true);
        }

        return false;
    }

    /**
     * Run
     *
     * Dispatch the route
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dispatch()
    {
        if (is_null($this->route)) {
            throw new Exception\RouteNotMatchedException(
                sprintf('%s expects a route to have been matched', __METHOD__)
            );
        }

        $arguments = $this->getArguments($this->path);

        // run the actual route
        $response = $this->getContainer()->resolve($this->getRoute()->getController(), $arguments);

        if (! $this->getRoute()->isClosure()) {
            $response = (new \ReflectionMethod($response, $this->getRoute()->getAction()))->invokeArgs($response, $arguments);
        }

        // send the response to the buffer
        if (! $response instanceof Response) {
            $response = new Response($response, 200, ['content-type' => 'text/html']);
        }

        return $response;
    }

    /**
     * Get Arguments
     *
     * Collate the arguments to pass in to the route callbacks
     *
     * @return array
     */
    public function getArguments()
    {
        $arguments = [];
        $segments  = explode('/', trim($this->path, '/'));

        if (! empty($segments)) {
            $keys = preg_grep('/\([^\/].*?\)/', $this->getRoute()->getUriSegments());
        }

        if (! empty($keys)) {
            foreach ($keys as $key => $val) {
                if (isset($segments[$key])) {
                    $arguments[] = $segments[$key];
                    continue;
                }

                $arguments[] = null;
            }
        }

        return $arguments;
    }

    /**
     * Get Route
     *
     * Return the matched route object
     *
     * @return \Orno\Mvc\Route\Route
     */
    public function getRoute()
    {
        if (is_null($this->route)) {
            throw new Exception\RouteNotMatchedException(
                sprintf('%s expects a route to have been matched', __METHOD__)
            );
        }

        return $this->route;
    }
}
