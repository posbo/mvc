<?php namespace Orno\Mvc\View;

use ArrayAccess;
use Orno\Mvc\View\RendererInterface;

class Renderer implements ArrayAccess, RendererInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $layout;

    /**
     * @var array
     */
    protected $regions = [];

    /**
     * Constructor
     *
     * @param string $layout
     */
    public function __construct($layout = null)
    {
        $this->layout = $layout;
    }

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
            throw new \InvalidArgumentException('A region must be provided with a region name');
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

        // are we assigning a view script to a region?
        if (file_exists($content)) {
            ob_start();
            include $content;
            $this->regions[$region][] = ob_get_contents();
            ob_end_clean();
            return;
        }

        // if we've got this far let's assume we are just assigning a string of
        // content to a region directly
        $this->regions[$region][] = $content;
        return;
    }

    /**
     * Render the template object and any data
     *
     * @return string
     */
    public function render($layout = null)
    {
        if (is_null($this->layout) && is_null($layout)) {
            throw new \RuntimeException('View could not be rendered as no layout was provided');
        }

        if (! is_null($layout)) {
            $this->layout = $layout;
        }

        ob_start();
        include $this->layout;
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
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
