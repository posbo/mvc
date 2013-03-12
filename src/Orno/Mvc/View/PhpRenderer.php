<?php namespace Orno\Mvc\View;

use ArrayAccess;
use Orno\Mvc\View\RendererInterface;
use Orno\Mvc\View\Template;

class PhpRenderer implements ArrayAccess, RendererInterface
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
    protected $snippets = [];

    /**
     * Constructor
     *
     * @param string $layout
     */
    public function __construct($layout = null)
    {
        $this->layout = $layout;
    }

    public function snippet($key = null, $path = null)
    {
        if (is_null($key) || is_null($path)) {
            throw new \InvalidArgumentException('A snippet must be provided with a key and a filepath');
        }

        $this->snippets[$key][] = $path;
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

        foreach ($this->snippets as $key => $paths) {
            ob_start();
            foreach ($paths as $path) {
                include $path;
            }
            $this->{$key} = ob_get_contents();
            ob_end_clean();
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
