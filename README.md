# Embryo Routing
A lightweight, fast and PSR compatible PHP Router.

# Features
* PSR (7, 11, 15) compatible.
* Static, dynamic and optional route patterns.
* Supports GET, POST, PUT, PATCH, DELETE and OPTIONS request methods.
* Before and after route middlewares.
* Supports grouping routes.
* Works in subfolders.

# Requirements
* PHP >= 7.1
* URL Rewriting
* A [PSR-7](https://www.php-fig.org/psr/psr-7/) http message implementation and [PSR-17](https://www.php-fig.org/psr/psr-17/) http factory implementation (ex. Embryo-Http)
* A [PSR-11](https://www.php-fig.org/psr/psr-11/) container implementation (ex. Embryo-Container)
* A [PSR-15](https://www.php-fig.org/psr/psr-15/) http server request handlers implementation (ex. Embryo-Middleware)

# Installation
Using Composer:
```
$ composer require davidecesarano/embryo-routing
```

# Usage
Before defining the application routes, it is necessary to create an instance of the `Container`, the `ServerRequest` and the `Response`.
```php
use Embryo\Container\Container;
use Embryo\Http\Emitter\Emitter;
use Embryo\Http\Factory\ServerRequestFactory;
use Embryo\Http\Factory\ResponseFactory;
use Embryo\Http\Server\MiddlewareDispatcher;
use Embryo\Routing\Router;

$container  = new Container;
$request    = (new ServerRequestFactory)->createServerRequestFromServer();
$response   = (new ResponseFactory)->createResponse(200);
```
Later you can define the routes with `Router` object and add the `Middleware` to the dispatcher.
```php
$router = new Router;
$router->get('/', function($request, $response){
    return $response->write('Hello World!');
});

$middleware = new MiddlewareDispatcher;
$middleware->add(new Embryo\Routing\Middleware\MethodOverrideMiddleware);
$middleware->add(new Embryo\Routing\Middleware\RoutingMiddleware($router));
$middleware->add(new Embryo\Routing\Middleware\RequestHandlerMiddleware($container));
$response = $middleware->dispatch($request, $response);
```
Finally you can produce output of the Response with Emitter object.
```php
$emitter = new Emitter;
$emitter->emit($response);
```

# Create routes
...

# Callbacks

# Placeholders

# Add middleware to route

# Set name route

# Create route groups

# Resolve via Container
