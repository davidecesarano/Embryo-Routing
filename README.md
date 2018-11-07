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

## Example
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
You may quickly test this using the built-in PHP server:
```
$ php -S localhost:8000
```
Going to http://localhost:8000 will now display "Hello World!".

## Create routes
You can define application routes using methods on the Router object. Every method accepts two arguments:
* The route pattern (with optional placeholders)
* The route callback (a clousure or a `class@method` string)
```php
// GET Route
$router->get('/blog/{id}', function($request, $response, $id) {
    return $response->write('This is post with id '.$id);
}
```

### Methods
Embryo Routing supports GET, POST, PUT, PATCH, DELETE and OPTIONS request methods. Every request method corresponds to a method of the Router class: `get()`, `post()`, `put()`, `patch()`, `delete()` and `options()`.
You can use `all()` and `match()` methods for supporting all methods or specific route methods.
```php
// All methods
$router->all('pattern', function($request, $response) {
    //...
}

// Match methods
$router->match(['GET', 'POST'], 'pattern', function($request, $response) {
    //...
}
```

### Overriding the request method
Use `X-HTTP-Method-Override` to override the HTTP Request Method. Only works when the original Request Method is POST. Allowed values for X-HTTP-Method-Override are `PUT`, `DELETE`, or `PATCH`. Embryo uses `MethodOverrideMiddleware` for manage HTTP-Method-Override.

## Callbacks

## Placeholders

## Add middleware to route

## Set name route

## Create route groups

## Resolve via Container

## Working in subfolder
