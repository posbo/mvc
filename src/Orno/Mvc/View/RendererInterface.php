<?php namespace Orno\Mvc\View;

interface RendererInterface
{
    /**
     * Import a php view as a snippet
     *
     * @param  string $key
     * @param  string $path
     * @return void
     */
    public function snippet($key = null, $path = null);

    /**
     * Render the view object
     *
     * @param  string $layout
     * @return void
     */
    public function render($layout = null);

    /**
     * Set a data value
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value);

    /**
     * Set a data value
     *
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value);

    /**
     * Get a data value
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key);

    /**
     * Get a data value
     *
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key);

    /**
     * Check if a data value is set
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key);

    /**
     * Check if a data value is set
     *
     * @param  string $key
     * @return boolean
     */
    public function offsetExists($key);

    /**
     * Unset a data value
     *
     * @param string $key
     */
    public function __unset($key);

    /**
     * Unset a data value
     *
     * @param string $key
     */
    public function offsetUnset($key);
}
