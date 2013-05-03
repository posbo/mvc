<?php namespace OrnoTest;

use Orno\Mvc\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadModulesThrowsExceptionWithNoModules()
    {
        $this->setExpectedException('Orno\Mvc\Exception\ModuleDefinitionException');
        $app = new Application;
        $app->loadModules();
    }

    public function testLoadModulesThrowsExceptionWithNoModuleSrcPath()
    {
        $this->setExpectedException('Orno\Mvc\Exception\ModuleDefinitionException');

        $config = ['modules' => ['Application' => []]];

        $app = new Application($config);
        $app->loadModules();
    }

    public function testLoadModulesThrowsExceptionWithBadlyFormedConfigFile()
    {
        $this->setExpectedException('Orno\Mvc\Exception\ModuleDefinitionException');

        $config = [
            'modules' => [
                'Application' => [
                    'src' => __DIR__ . '/Assets/Application/src',
                    'config' => __DIR__ . '/Assets/Application/bad-config'
                ]
            ]
        ];

        $app = new Application($config);
        $app->loadModules();
    }

    public function testLoadModulesResolvesConfigPath()
    {
        $config = [
            'modules' => [
                'Application' => [
                    'src' => __DIR__ . '/Assets/Application/src'
                ],
                'SecondModule' => [
                    'src' => __DIR__ . '/Assets/SecondModule/src'
                ]
            ]
        ];

        $app = new Application($config);
        $app->loadModules();

        $config = $this->readAttribute($app, 'config');
        $moduleConfig = $this->readAttribute($app, 'moduleConfig');

        $this->assertArrayHasKey('dependencies', $config);
        $this->assertArrayHasKey('Application', $moduleConfig);
        $this->assertArrayHasKey('dependencies', $moduleConfig['Application']);
    }

    /**
     * @runInSeperateProcess
     */
    public function testApplicationRuns()
    {
        $config = [
            'modules' => [
                'Application' => [
                    'src' => __DIR__ . '/Assets/Application/src'
                ],
                'SecondModule' => [
                    'src' => __DIR__ . '/Assets/SecondModule/src'
                ]
            ]
        ];

        (new Application($config))->run();
    }
}
