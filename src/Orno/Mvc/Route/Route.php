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

/**
 * Route
 *
 * A single route object
 */
class Route
{
    /**
     * Access to the container
     */
    use ContainerAwareTrait;

    /**
     * The route path
     *
     * @var string
     */
    protected $path;

    /**
     * The path from the incoming request
     *
     * @var string
     */
    protected $pathInfo;

    /**
     * The regex pattern to match the route against
     *
     * @var string
     */
    protected $regex;

    /**
     * Points to item registered with Orno\Di\Container
     *
     * @var string
     */
    protected $controller;

    /**
     * The name of the action to invoke
     *
     * @var string
     */
    protected $action;

    /**
     * The request method this route should respond to
     *
     * @var string
     */
    protected $method;

    /**
     * The scheme for which the route should respond to
     *
     * @var string
     */
    protected $scheme = 'http';

    /**
     * The module that the controller resides in
     *
     * @var string
     */
    protected $module = null;

    /**
     * Is the controller a closure?
     *
     * @var boolean
     */
    protected $closure;

    /**
     * Constructor
     *
     * @param string  $route
     * @param string  $controller
     * @param string  $action
     * @param string  $method
     * @param boolean $closure
     */
    public function __construct(
        $path       = null,
        $controller = null,
        $action     = null,
        $method     = null,
        $closure    = false
    ) {
        $this->path       = $path;
        $this->controller = $controller;
        $this->action     = $action;
        $this->method     = strtoupper($method);
        $this->closure    = (bool) $closure;
    }

    /**
     * Build the regex to patch against path info
     *
     * @return void
     */
    public function setRegex()
    {
        $segments = explode('/', trim($this->path, '/'));

        // differentiate between optional and required wildcard segments
        $required = preg_grep('/\([^\?].*?\)/', $segments);
        $optional = preg_grep('/\((\?.*?)\)/', $segments);

        $wildcards = $required + $optional;

        // loop through wildcards and replace with appropriate regex
        foreach ($wildcards as $key => $value) {
            $segments[$key] = preg_match('/(\?.*?)/', $value) ? '(\\/[^\\/]+?)?' : '([^\\/]+?)';
        }

        // build the full regex to match against the path info
        $this->regex = implode('/', $segments);
        $this->regex = '#^/' . str_replace('/(\/', '(\\/', $this->regex) . '$#';
    }

    /**
     * Check if this route is a regex match
     *
     * @param  string $pathInfo
     * @return boolean
     */
    public function isRegexMatch($pathInfo)
    {
        $this->pathInfo = $pathInfo;

        if (is_null($this->regex)) {
            $this->setRegex();
        }

        return (bool) preg_match($this->regex, $pathInfo);
    }

    /**
     * Check if the route matches the request method
     *
     * @param  string $method
     * @return boolean
     */
    public function isMethodMatch($method)
    {
        return (strtoupper($method) === $this->method);
    }

    /**
     * What scheme should the route respond to, currently only has support for
     * HTTP and HTTPS, default will always be HTTP unless explicitly set to HTTPS
     *
     * @param  string $scheme
     * @return void
     */
    public function setScheme($scheme = 'http')
    {
        $this->scheme = ($scheme === 'https') ? 'https' : 'http';
    }

    /**
     * Check if the route matches the scheme
     *
     * @param  string $scheme
     * @return boolean
     */
    public function isSchemeMatch($scheme)
    {
        return ($scheme === $this->scheme);
    }

    /**
     * Set Module
     *
     * Use the first level of the controller namespace as the module
     *
     * @return void
     */
    protected function setModule()
    {
        if (strpos(trim($this->controller, '\\'), '\\') !== false) {
            $this->module = explode('\\', trim($this->controller, '\\'))[0];
        }
    }

    /**
     * Get Module
     *
     * Return the module that the controller resides in
     *
     * @return string
     */
    public function getModule()
    {
        if (is_null($this->module)) {
            $this->setModule($this->controller);
        }

        return $this->module;
    }

    /**
     * Get Controller
     *
     * Return the controller name (alias registered with the container)
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get Action
     *
     * Return the action name
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Is Closure?
     *
     * @return boolean
     */
    public function isClosure()
    {
        return $this->closure;
    }

    /**
     * Build an arguments array to pass in named arguments to the action
     *
     * @param  \ReflectionMethod $method
     * @return array
     */
    public function getArguments(\ReflectionMethod $method = null)
    {
        $arguments = [];
        $named = [];

        // split the path and request path strings
        $segments = explode('/', trim($this->path, '/'));
        $values = explode('/', trim($this->pathInfo, '/'));

        // grep for wildcards and map names to values
        foreach (preg_grep('#^\\((.+)\\)$#', $segments) as $key => $segment) {
            $name = str_replace(['(', ')', '?'], null, $segment);

            if (isset($values[$key])) {
                $named[$name] = $values[$key];
            }

            // if we don't have a method to reflect on we just pass in arguments
            // by order rather than by name
            if (is_null($method)) {
                $arguments[] = (array_key_exists($key, $values)) ? $values[$key] : null;
            }
        }

        // again, if no method to reflect on, just return the indexed array
        if (is_null($method)) {
            return $arguments;
        }

        // reflect on parameters and build arguments array
        foreach ($method->getParameters() as $param) {
            if (array_key_exists($param->getName(), $named)) {
                $arguments[] = $named[$param->getName()];
                continue;
            }

            // if the wildcard is optional and not provided, get the default value
            if ($param->isOptional()) {
                $arguments[] = $param->getDefaultValue();
            }
        }

        return $arguments;
    }

    /**
     * Dispatch the route
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dispatch()
    {
        $response = $this->getContainer()->resolve($this->getController(), $this->getArguments());

        if (! $this->isClosure()) {
            $action = new \ReflectionMethod($response, $this->getAction());
            $response = $action->invokeArgs($response, $this->getArguments($action));
        }

        if (! $response instanceof Response) {
            $response = new Response($response);
        }

        return $response;
    }
}
