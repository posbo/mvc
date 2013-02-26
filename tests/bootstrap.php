<?php

class Bootstrap
{
    /**
     * Array of registered namespace => path pairs
     * @var array
     */
    protected $namespaces = [];

    /**
     * Register a namespace => path pair with the autoloader
     *
     * @param  array     $namespaces
     * @return Bootstrap $this
     */
    public function addNamespaces(array $namespaces)
    {
        foreach ($namespaces as $namespace => $path) {
            $this->namespaces[$namespace] = $path;
        }
        return $this;
    }

    /**
     * Resolves a class file from a provided class
     *
     * @param  string  $class
     * @return string
     */
    public function resolveFile($class)
    {
        // loop through the namespace registry
        foreach ($this->namespaces as $namespace => $path) {
            $length = strlen($namespace);

            if (substr($class, 0, $length) !== $namespace) {
                continue;
            }

            return rtrim($path, '/') . DIRECTORY_SEPARATOR . $this->classToFile($class);
        }
    }

    /**
     * Converts a class name to a filename
     *
     * @param  string  $class
     * @return string
     */
    public function classToFile($class)
    {
        return str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    }

    /**
     * Checks to see if a class is already loaded
     *
     * @param  string  $class
     * @return boolean
     */
    public function isLoaded($class)
    {
        return class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false);
    }

    /**
     * Autoload method to be registered with the spl autoload stack
     *
     * @param  string  $class
     * @return void
     */
    public function autoload($class)
    {
        // is the class already loaded?
        if ($this->isLoaded($class)) {
            return;
        }

        $file = $this->resolveFile($class);

        if ($file !== null) {
            include $file;
        }
    }

    /**
     * Registers this autoloader with the spl autoload stack
     * @param  boolean  $prepend
     * @return void
     */
    public function register($prepend = true)
    {
        spl_autoload_register([$this, 'autoload'], true, (bool) $prepend);
    }
}

(new Bootstrap)->addNamespaces([
    'Orno\Mvc' => __DIR__ . '/../src'
])->register();
