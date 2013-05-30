<?php
/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 */
namespace Orno\Mvc;

use Orno\Di\ContainerAwareTrait;
use Orno\Http\RequestInterface;
use Orno\Http\ResponseInterface;
use Orno\Http\Request;
use Orno\Http\Response;

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
     * Constructor
     *
     * @param \Orno\Http\RequestInterface $request
     */
    public function __construct(RequestInterface $request = null)
    {
        $this->getContainer()->register('request', 'Orno\Http\Request', true)
             ->withArguments([$_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER]);
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
     * @param  string|\Closure $editor - sublime|emacs|textmate|macvim
     * @return void
     */
    public function setExceptionHandler($editor = null)
    {
        $handler = ($this->getContainer()->resolve('request')->isAjax())
                 ? 'Whoops\Handler\JsonResponseHandler'
                 : 'Whoops\Handler\PrettyPageHandler';

        if (is_null($editor)) {
            $this->getContainer()->register('exception_handler', $handler);
        } else {
            $this->getContainer()->register('exception_handler', $handler)
                                 ->withMethodCall('setEditor', [$editor]);
        }

        $this->getContainer()->register('whoops', 'Whoops\Run')
                             ->withMethodCall('pushHandler', ['exception_handler'])
                             ->withMethodCall('register');

        $this->getContainer()->resolve('whoops');
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

        $this->getContainer()->register('router', 'Orno\Mvc\Route\RouteCollection', true)
                             ->withMethodCall('setRoutes', [$routes]);
    }

    /**
     * Return a 404 response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function notFoundException()
    {
        $router = $this->getContainer()->resolve('router');

        // try to match any custom 404 route
        $route = $router->match('/404');

        // set response body
        $body = ($route !== false) ? $route->dispatch()->getContent() : 'Error 404 - Page Not Found!';

        // send the 404 response
        return (new Response($body, 404))->send();
    }

    /**
     * Run
     *
     * Let's Go!
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function run()
    {
        $request = $this->getContainer()->resolve('request');
        $router = $this->getContainer()->resolve('router');

        // match the route
        $route = $router->match($request->getPathInfo(), $request->getMethod(), $request->getScheme());

        // if no match throw a 404
        if ($route === false) {
            return $this->notFoundException();
        }

        // give active module config priority
        if (
            array_key_exists($route->getModule(), $this->config)
            && array_key_exists('dependencies', $this->config[$route->getModule()])
        ) {
            $this->setDependencyConfig($this->config[$route->getModule()]['dependencies']);
        }

        // dispatch the route
        return $route->dispatch()->send();
    }
}
