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


    }
}
