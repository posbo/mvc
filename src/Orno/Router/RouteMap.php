<?php namespace Orno\Router;

use Orno\Di\ContainerAwareTrait;
use Closure;

class RouteMap
{
    /**
     * Get access to the container object
     */
    use ContainerAwareTrait;

    /**
     * @var array
     */
    protected $map;

    /**
     * Map a route to a destination
     *
     * @param  string         $path
     * @param  string|closure $destination
     * @param  string         $method
     * @return void
     */
    public function route($path, $destination, $method = 'GET')
    {
        $method = strtoupper($method);
        $this->map[$method][$path] = [];

        if (is_string($destination)) {
            $split = explode('@', $destination);

            if (count($split) < 2) {
                throw \InvalidArgumentException('Route destinations must follow the pattern "Controller@Action"');
            }

            $this->map[$method][$path]['controller'] = $split[0];
            $this->map[$method][$path]['action']     = $split[1];
        }

        if ($destination instanceof Closure) {
            $this->getContainer()->register($path, $destination);
        }
    }

    /**
     * Map a GET route to a destination
     *
     * @param  string         $path
     * @param  string|closure $destination
     * @return void
     */
    public function get($path, $destination)
    {
        $this->route($path, $destination, 'GET');
    }

    /**
     * Map a POST route to a destination
     *
     * @param  string         $path
     * @param  string|closure $destination
     * @return void
     */
    public function post($path, $destination)
    {
        $this->route($path, $destination, 'POST');
    }

    /**
     * Map a PUT route to a destination
     *
     * @param  string         $path
     * @param  string|closure $destination
     * @return void
     */
    public function put($path, $destination)
    {
        $this->route($path, $destination, 'PUT');
    }

    /**
     * Map a DELETE route to a destination
     *
     * @param  string         $path
     * @param  string|closure $destination
     * @return void
     */
    public function delete($path, $destination)
    {
        $this->route($path, $destination, 'DELETE');
    }

    /**
     * Return the route map array
     *
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }
}
