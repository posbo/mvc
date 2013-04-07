<?php namespace Orno\Mvc\View;

use Orno\Di\ContainerAwareTrait;
use ArrayAccess;

abstract class AbstractRenderer implements ArrayAccess
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    protected $layouts = [];

    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $regions = [];

    /**
     * Add a view script path to the Renderer object
     *
     * @param  string|array $paths
     * @return void
     */
    public function addViewPath($paths)
    {
        foreach ((array) $paths as $path) {
            $this->paths[] = $path;
        }
    }

    /**
     * Add a layout to the Renderer object
     *
     * @param  array $layouts
     * @return void
     */
    public function addLayout(array $layout)
    {
        foreach ($layout as $key => $value) {
            $this->layouts[$key] = $value;
        }
    }

    /**
     * Render a view with an optional layout
     *
     * @param  string $layout
     * @return string
     */
    abstract public function render($layout = null);

    /**
     * Set or write to a region
     *
     * @param  string  $region
     * @param  string  $content
     * @param  array   $data
     * @param  boolean $render
     * @return void
     */
    public function region($region = null, $content = null, array $data = [])
    {
        if (is_null($region)) {
            throw new Exception\RegionNotProvidedException(
                'A region must be provided with a region name'
            );
        }

        if (empty($this->paths)) {
            throw new Exception\ViewPathNotProvidedException(
                'The Renderer must be provided with at least 1 view path'
            );
        }

        // are we simply rendering a region?
        if (is_null($content)) {
            if (isset($this->regions[$region])) {
                ob_start();
                foreach ($this->regions[$region] as $region) {
                    echo $region;
                }
                $content = ob_get_contents();
                ob_end_clean();
            }
            echo (! is_null($content)) ? $content : '';
            return;
        }

        // do we have any data to set?
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        // loop through the view paths to find the view script
        foreach ($this->paths as $path) {
            $viewScript = rtrim($path, '/') . '/' . ltrim($content, '/') . '.php';
            if (file_exists($viewScript)) {
                ob_start();
                include $viewScript;
                $this->regions[$region][] = ob_get_contents();
                ob_end_clean();
                return;
            }
        }

        // if we've got this far let's assume we are just assigning a string of
        // content to a region directly
        $this->regions[$region][] = $content;
        return;
    }

    /**
     * Magic call method for view helpers
     *
     * @param  string $helper
     * @param  array  $args
     * @return mixed
     */
    public function __call($helper, $args)
    {
        try {
            $helper = $this->getContainer()->resolve($helper, $args);
        } catch (Exception $e) {
            throw new Exception\HelperNotFoundException(sprintf(
                'The helper %s could not be found', $helper
            ));
        }

        return $helper;
    }

    /**
     * Set a data value
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Set a data value
     *
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Get a data value
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data[$key];
    }

    /**
     * Get a data value
     *
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    /**
     * Check if a data value is set
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Check if a data value is set
     *
     * @param  string $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Unset a data value
     *
     * @param string $key
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Unset a data value
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }
}
