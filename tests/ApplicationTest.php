<?php namespace OrnoTest;

use Orno\Mvc\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadModulesThrowsExceptionWithNoModules()
    {
        $this->setExpectedException('Orno\Mvc\Exception\ModuleDefinitionException');
        $app = new Application;
        $app->loadModules([]);
    }

    public function testLoadModulesThrowsExceptionWithNoModuleSrcPath()
    {
        $this->setExpectedException('Orno\Mvc\Exception\ModuleDefinitionException');

        $config = ['Application' => []];

        $app = new Application;
        $app->loadModules($config);
    }

    public function testLoadModulesThrowsExceptionWithBadlyFormedConfigFile()
    {
        $this->setExpectedException('Orno\Mvc\Exception\ModuleDefinitionException');

        $config = [
            'Application' => [
                'src' => __DIR__ . '/Assets/Application/src',
                'config' => __DIR__ . '/Assets/Application/bad-config'
            ]
        ];

        $app = new Application;
        $app->loadModules($config);
    }

    public function testLoadModulesResolvesConfigPath()
    {
        $config = [
            'Application' => [
                'src' => __DIR__ . '/Assets/Application/src'
            ],
            'SecondModule' => [
                'src' => __DIR__ . '/Assets/SecondModule/src'
            ]
        ];

        $app = new Application;
        $app->loadModules($config);

        $config = $this->readAttribute($app, 'config');

        $this->assertArrayHasKey('dependencies', $config);
    }

    public function testFullBoostrapProcessRuns()
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                        ->disableOriginalConstructor()
                        ->getMock();

        $request->expects($this->once())
                ->method('getPathInfo')
                ->will($this->returnValue('/hello/phil'));

        $request->expects($this->once())
                ->method('getMethod')
                ->will($this->returnValue('GET'));

        $request->expects($this->once())
                ->method('getScheme')
                ->will($this->returnValue('http'));

        $request->expects($this->once())
                ->method('isXmlHttpRequest')
                ->will($this->returnValue(false));

        $app = new Application($request);

        $routes = [
            '/hello/(name)' => function ($name) {
                return 'Hello ' . ucwords($name);
            }
        ];

        $app->setExceptionHandler('sublime');
        $app->registerAutoloader();
        $app->registerRouter($routes);

        ob_start();
        $app->run();
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertSame($content, 'Hello Phil');
    }
}
