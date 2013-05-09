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
use Symfony\Component\HttpFoundation\Response;

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

    public function __construct()
    {
        $this->getContainer()->register('Symfony\Component\HttpFoundation\Request', function () {
            return Request::createFromGlobals();
        }, true);
    }

    /**
     * Load Modules
     *
     * Load modules and module specific config into the application object
     *
     * @throws \Orno\Mvc\Exception\ModuleDefinitionException
     * @return void
     */
    public function loadModules(array $config)
    {
        if (empty($config)) {
            throw new Exception\ModuleDefinitionException(
                'No modules were defined in the application configuration array'
            );
        }

        array_walk($config, function ($options, $module) {
            if (! isset($options['src'])) {
                throw new Exception\ModuleDefinitionException(
                    sprintf('Module (%s) must have a [src] key defined in the application configuration array', $module)
                );
            }

            // build namespace autoloader config array
            $this->config['autoload_namespaces'][$module] = $options['src'];

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
    protected function mergeModuleConfig($configPath, $module) {
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

                    $this->config[$module][$key] = $config;
                }
            }
        }

        $this->config = array_merge($this->config, $this->config[$module]);
    }

    /**
     * Set Exception Handler
     *
     * Register and start the application exception handler
     *
     * @return void
     */
    public function setExceptionHandler($editor = null)
    {
        $handler = ($this->getContainer()->resolve('Symfony\Component\HttpFoundation\Request')->isXmlHttpRequest())
                 ? 'Whoops\Handler\JsonResponseHandler'
                 : 'Whoops\Handler\PrettyPageHandler';

        $this->getContainer()->register('Handler', $handler);

        $this->getContainer()->register('Whoops\Run')
                             ->withMethodCall('pushHandler', ['Handler'])
                             ->withMethodCall('register');

        $this->getContainer()->resolve('Whoops\Run');
    }

    /**
     * Set Dependency Config
     *
     * Build the container with dependencies configuration
     *
     * @return void
     */
    public function setDependencyConfig(array $config)
    {
        if (isset($this->config['dependencies'])) {
            $config = array_merge($this->config['dependencies'], $config);
        }

        $this->getContainer()->setConfig($config);
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
    public function registerRouter(array $routes = [])
    {
        $routes = array_merge($this->config['routes'], $routes);

        $this->getContainer()->register('Orno\Mvc\Route\RouteCollection', null, true)
             ->withMethodCall('setRoutes', [$routes]);

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
        // start the dispatch process
        $dispatcher = $this->getContainer()->resolve('dispatcher');

        if (! $dispatcher->match($this->getContainer()->resolve('Symfony\Component\HttpFoundation\Request'))) {
            // do we have a custom 404?
            if (! $dispatcher->match($request, true, true)) {
                $response = new Response('Error 404 - Page Not Found', 404);
            }
        } else {
            $module = $dispatcher->getRoute()->getModule();

            if (isset($this->config[$module]['dependencies'])) {
                $this->setDependencyConfig($this->config[$module]['dependencies']);
            }
        }

        if (! isset($response)) {
            $response = $dispatcher->dispatch();
        }

        $response->send();
    }
}
