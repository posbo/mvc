<?php namespace Orno\Mvc\Route;

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
     * Constructor
     *
     * @param string $path
     * @param string $method
     */
    public function __construct($path = null, $method = null)
    {
        if (is_array($path)) {
            return $this->setEnvironment();
        }

        $this->path = $path;
        $this->method = $method;

        $this->setSegments();
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
     * @param array $server
     */
    public function setEnvironment(array $server = [])
    {

    }
}
