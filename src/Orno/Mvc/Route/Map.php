<?php namespace Orno\Mvc\Route;

use Closure;

class Map
{
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
    public function route($path, $destination, $method = null)
    {
        $this->map[$path] = [];

        if (! is_null($method)) {
            $this->map[$path]['method'] = strtoupper($method);
        }

        if (is_string($destination)) {
            $split = explode('@', $destination);

            if (count($split) > 1) {
                $this->map[$path]['action'] = $split[1];
            }

            $this->map[$path]['controller'] = $split[0];
        }

        if ($destination instanceof Closure) {
            $this->map[$path]['controller'] = $destination;
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
