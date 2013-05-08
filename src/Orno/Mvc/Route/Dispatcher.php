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
     * @param  boolean $get - whether to fall back to the 'GET' request method
     * @param  boolean $notFound - set the route as a 404
     * @return boolean
     */
    public function match(Request $request, $get = false, $notFound = false)
    {
        $this->path = ($notFound === false) ? $request->getPathInfo() : '/404';

        $method = ($get === true) ? 'GET' : $request->getMethod();
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

        // if we haven't matched a route for a request method other than GET,
        // we fall back to the GET method
        if ($get === false) {
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

        $response = $this->getContainer()->resolve($this->getRoute()->getController(), $arguments);

        if (! $this->getRoute()->isClosure()) {
            $response = (new \ReflectionMethod($response, $this->getRoute()->getAction()))->invokeArgs($response, $arguments);
        }

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
            $keys = preg_grep('/\(.*?\)/', $this->getRoute()->getUriSegments());
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
