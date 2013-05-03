<?php
/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 */
namespace Orno\Mvc;

use Orno\Di\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Application
 */
class Application
{
    /**
     * Access to the container
     */
    use ContainerAwareTrait;

    /**
     * Main config array
     *
     * @var array
     */
    protected $config = [
        'modules' => [],
        'dependencies' => [],
        'routes' => [],
        'autoload_namespaces' => [],
        'autoload_classmap' => []
    ];

    /**
     * Module specific config, eventually merged into main config array
     *
     * @var array
     */
    protected $moduleConfig = [];

    /**
     * Constructor
     *
     * @param string $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Load Modules
     *
     * Load modules and module specific config into the application object
     *
     * @throws \Orno\Mvc\Exception\ModuleDefinitionException
     * @return void
     */
    public function loadModules()
    {
        if (! isset($this->config['modules']) || empty($this->config['modules'])) {
            throw new Exception\ModuleDefinitionException(
                'No modules were defined in the application configuration array'
            );
        }

        array_walk($this->config['modules'], function ($options, $module) {
            if (! isset($options['src'])) {
                throw new Exception\ModuleDefinitionException(
                    sprintf('Module (%s) must have a "src" key defined in the application configuration array', $module)
                );
            }

            // set any module specific config paths
            if (isset($options['config'])) {
                $configPath = $options['config'];
            } elseif (is_dir(dirname($options['src']) . '/config')) {
                $configPath = dirname($options['src']) . '/config';
            }

            if (isset($configPath)) {
                $this->mergeModuleConfig($configPath, $module);
            }
        });
    }

    /**
     * Merge Module Config
     *
     * Accepts a path and a module name to merge in all module specific config files
     *
     * @throws \Orno\Mvc\Exception\ModuleDefinitionException
     * @param  string $configPath
     * @param  string $module
     * @return void
     */
    public function mergeModuleConfig($configPath, $module) {
        foreach (new \DirectoryIterator($configPath) as $file) {
            if ($file->isFile()) {
                $key = $file->getBasename('.php');
                $ext = str_replace($key, null, $file->getBasename());

                if ($ext === '.php') {
                    $config = include $file->getPathname();

                    if (! is_array($config)) {
                        throw new Exception\ModuleDefinitionException(
                            sprintf('The file %s must return a configuration array', $file->getPathname())
                        );
                    }

                    $this->moduleConfig[$module][$key] = $config;
                }
            }
        }

        $this->config = array_merge($this->config, $this->moduleConfig[$module]);
    }

    /**
     * Set Exception Handler
     *
     * Register and start the application exception handler
     *
     * @return boolean
     */
    public function setExceptionHandler()
    {
        // todo
        return false;
    }

    /**
     * Set Dependency Config
     *
     * Build the container with dependencies configuration
     *
     * @return void
     */
    public function setDependencyConfig()
    {
        $this->getContainer()->setConfig($this->config['dependencies']);
    }

    /**
     * Register Autoloader
     *
     * Apply autoloader config and register the application autoloader
     *
     * @return void
     */
    public function registerAutoloader()
    {
        $this->getContainer()->register('autoloader', 'Orno\Loader\Autoloader')
             ->withMethodCall('registerNamespaces', [$this->config['autoload_namespaces']])
             ->withMethodCall('registerClasses', [$this->config['autoload_classmap']]);

        $this->getContainer()->resolve('autoloader')->register();
    }

    /**
     * Register Router
     *
     * Build a route collection and pass it to the dispatcher
     *
     * @return void
     */
    public function registerRouter()
    {
        $this->getContainer()->register('Orno\Mvc\Route\RouteCollection', null, true)
             ->withMethodCall('setRoutes', [$this->config['routes']]);

        $this->getContainer()->register('dispatcher', 'Orno\Mvc\Route\Dispatcher')
             ->withArgument('Orno\Mvc\Route\RouteCollection');
    }

    /**
     * Run
     *
     * Let's Go!
     *
     * @return void
     */
    public function run()
    {
        // register the exception handler
        $this->setExceptionHandler();
        // load all modules and config
        $this->loadModules();
        // set config for the di container (will later be merged with active module config)
        $this->setDependencyConfig();
        // register the autoloader
        $this->registerAutoloader();
        // register the router
        $this->registerRouter();

        // start the dispatch process
        $dispatcher = $this->getContainer()->resolve('dispatcher');

        if ($dispatcher->match(Request::createFromGlobals())) {
            $module = $dispatcher->getRoute()->getModule();

            if (isset($this->moduleConfig[$module]['dependencies'])) {
                $this->config = array_merge($this->config, $this->moduleConfig[$module]);
                $this->setDependencyConfig($this->moduleConfig[$module]['dependencies']);
            }

            return $dispatcher->dispatch()->send();
        }
    }
}
