# Orno\Mvc

Orno\Mvc is a Model/View/Controller and Routing layer to build basic applications. It uses [Orno\Di](https://github.com/orno/di) to configure routed objects making development faster and easier.

## Basic Usage

Set a basic route and launch dispatch it.

```php
<?php

$route = new Orno\Mvc\Route\RouteCollection;

$route->add('/hello', function () {
    return 'Hello World';
});

// build the dispatcher, set the environment with the $_SERVER array and dispatch
(new Orno\Mvc\Route\Dispatcher($route))->setEnvironment($_SERVER)->run();
```

## Route Parameters

You can pass parameters to your route callback.

```php
<?php

$route = new Orno\Mvc\Route\RouteCollection;

$route->add('/hello/(name)', function ($name) {
    return 'Hello ' . $name;
});

// build the dispatcher, set the environment with the $_SERVER array and dispatch
(new Orno\Mvc\Route\Dispatcher($route))->setEnvironment($_SERVER)->run();
```

#### Optional Parameters

To make a parameter optional, simple prefix it with a ?

```php
<?php

$route = new Orno\Mvc\Route\RouteCollection;

$route->add('/hello/(?name)', function ($name) {
    $name = (isset($name)) ? $name : 'Phil';
    return 'Hello ' . $name;
});

// build the dispatcher, set the environment with the $_SERVER array and dispatch
(new Orno\Mvc\Route\Dispatcher($route))->setEnvironment($_SERVER)->run();
```

## Controller Action Routes

You can route to a controller action instead of keeping all of your logic in a routes file.

> Assuming your controllers are defined in a modular namespaced structure.

`Controller`

```php
<?php namespace Application\Controller;

class HomeController
{
    public function helloAction($name)
    {
        return 'Hello ' . $name;
    }
}
```

`Routing`

```php
<?php

$route = new Orno\Mvc\Route\RouteCollection;

$route->add('/hello/(name)', 'Application\Controller\HomeController::helloAction');

// build the dispatcher, set the environment with the $_SERVER array and dispatch
(new Orno\Mvc\Route\Dispatcher($route))->setEnvironment($_SERVER)->run();
```

## HTTP Method Specific Routes

You can create routes that act differently based on the HTTP request method.

```php
<?php

$route = new Orno\Mvc\Route\RouteCollection;

$route->get('/login', 'Application\Controller\LoginController::loginFormAction');
$route->post('/login', 'Application\Controller\LoginController::processLoginFormAction');

// build the dispatcher, set the environment with the $_SERVER array and dispatch
(new Orno\Mvc\Route\Dispatcher($route))->setEnvironment($_SERVER)->run();
```

## RESTful Routes

The router has a helper method that will speed up the process of creating routes for a RESTful API.

```php
<?php

$route = new Orno\Mvc\Route\RouteCollection;

// the route is simply pointed to a controller
$route->restful('/user', 'Application\Controller\UserController');

// build the dispatcher, set the environment with the $_SERVER array and dispatch
(new Orno\Mvc\Route\Dispatcher($route))->setEnvironment($_SERVER)->run();
```

Calling the restful method is the equivelant of setting the following routes.

```php
$route->get('/user', 'Application\Controller\UserController::getAll'); // display all user records
$route->get('/user/(id)', 'Application\Controller\UserController::get'); // display 1 user record by id
$route->post('/user', 'Application\Controller\UserController::create'); // create a new user record
$route->put('/user/(id)', 'Application\Controller\UserController::update'); // update 1 user record
$route->patch('/user/(id)', 'Application\Controller\UserController::update'); // update 1 user record
$route->delete('/user/(id)', 'Application\Controller\UserController::delete'); // delete a user record
$route->options('/user', 'Application\Controller\UserController::options'); // return a header with the api methods available
```

A [RestfulControllerInterface](/orno/mvc/blob/master/src/Orno/Mvc/Controller/RestfulControllerInterface.php) is included in the component to help you build RESTful controllers.
