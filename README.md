# Embryo Routing
A lightweight, fast and PSR compatible PHP Router.

## Features
* PSR (7, 11, 15) compatible.
* Static, dynamic and optional route patterns.
* Supports GET, POST, PUT, PATCH, DELETE and OPTIONS request methods.
* Supports route middlewares.
* Supports grouping routes.
* Supports resolve via Container.
* Works in subfolders.

## Requirements
* PHP >= 7.1
* URL Rewriting
* A [PSR-7](https://www.php-fig.org/psr/psr-7/) http message implementation and [PSR-17](https://www.php-fig.org/psr/psr-17/) http factory implementation (ex. [Embryo-Http](https://github.com/davidecesarano/Embryo-Http))
* A [PSR-11](https://www.php-fig.org/psr/psr-11/) container implementation (ex. [Embryo-Container](https://github.com/davidecesarano/Embryo-Container))
* A [PSR-15](https://www.php-fig.org/psr/psr-15/) http server request handlers implementation (ex. [Embryo-Middleware](https://github.com/davidecesarano/Embryo-Middleware))

## Installation
Using Composer:
```
$ composer require davidecesarano/embryo-routing
```
## Example
Before defining the application routes, it is necessary to create the following objects: 
* the `Container`
* the `ServerRequestFactory`
* the `ResponseFactory`
* the `RequestHandler`
* the `Emitter`

```php
use Embryo\Container\Container;
use Embryo\Http\Emitter\Emitter;
use Embryo\Http\Factory\{ServerRequestFactory, ResponseFactory};
use Embryo\Http\Server\RequestHandler;
use Embryo\Routing\Router;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$container      = new Container;
$request        = (new ServerRequestFactory)->createServerRequestFromServer();
$response       = (new ResponseFactory)->createResponse(200);
$requestHandler = new RequestHandler;
$emitter        = new Emitter;
```
Later, you can define the routes with `Router` object:
```php
$router = new Router;

$router->get('/', function(Request $request, Response $response){
    $response->getBody()->write('Hello World!');
    return $response;
});
```
Now, create PSR-15 middleware queue adding required routing middlewares:
* `MethodOverrideMiddleware` for overriding the HTTP Request Method.
* `RoutingMiddleware` for match route and handler discovery.
* `RequestHandlerMiddleware` for executing request handlers discovered by router.
```php
$requestHandler->add(new Embryo\Routing\Middleware\MethodOverrideMiddleware);
$requestHandler->add(new Embryo\Routing\Middleware\RoutingMiddleware($router));
$requestHandler->add(new Embryo\Routing\Middleware\RequestHandlerMiddleware($container));
$response = $requestHandler->dispatch($request, $response);
```
Finally you can produce output of the Response with `Emitter` object.
```php
$emitter->emit($response);
```

## Usage
* [Create routes](#create-routes)
* [Callbacks](#callbacks)
* [Placeholders](#placeholders)
* [Set name route](#set-name-route)
* [Create route groups](#create-route-group)
* [Add middleware to route](#add-middleware-to-route)
* [Resolve via Container](#resolve-via-container)
* [Set default namespace](#set-default-namespace)
* [Working in subfolder](#working-in-subdirectory)

### Create routes
You can define application routes using methods on the Router object. Every method accepts two arguments:
* The route pattern (with optional placeholders)
* The route callback (a closure or a `class@method` string)
```php
// GET Route
$router->get('/blog/{id}', function(Request $request, Response $response, int $id) {
    $response->getBody()->write('This is post with id '.$id);
    return $response;
});
```
Note that you can write pattern with or without "/" to first character like this: `blog/{id}`.

#### Methods
Embryo Routing supports GET, POST, PUT, PATCH, DELETE and OPTIONS request methods. Every request method corresponds to a method of the Router object: `get()`, `post()`, `put()`, `patch()`, `delete()` and `options()`.
You can use `all()` and `map()` methods for supporting all methods or specific route methods.
```php
// All methods
$router->all('pattern', function(Request $request, Response $response) {
    //...
});

// Match methods
$router->map(['GET', 'POST'], 'pattern', function(Request $request, Response $response) {
    //...
});
```

#### Overriding the request method
Use `X-HTTP-Method-Override` to override the HTTP Request Method. Only works when the original Request Method is POST. Allowed values for X-HTTP-Method-Override are `PUT`, `DELETE`, or `PATCH`. Embryo uses `MethodOverrideMiddleware` for manage HTTP-Method-Override.

### Callbacks
Each routing method accepts a callback routine as its final argument. This argument by default it accepts at least two arguments:
* **Request**. The first argument is a `Psr\Http\Message\ServerRequestInterface` object that represents the current HTTP request.
* **Response**. The second argument is a `Psr\Http\Message\ResponseInterface` object that represents the current HTTP response.
* **Placeholder(s)**. Each route placeholder as a separate argument.

#### Writing content to the response
There are three ways you can write content to the HTTP response:
1. You can simply `echo()` content from the route callback: this content will be appended to the current HTTP response object.
2. You can return a `Psr\Http\Message\ResponseInterface` object.
3. You can return a `json` content when returning an array.
```php
// echo
$router->get('/hello/{name}', function (Request $request, Response $response, string $name) {
    echo $name;
});

// response object
$router->get('/hello/{name}', function (Request $request, Response $response, string $name) {
    $response->getBody()->write($name);
    return $response;
});

// json
$router->get('/hello/{name}', function ($Request $request, Response $response, string $name) {
    return [
        'name' => $name
    ];
});
```

#### Closure binding
If you use a Closure instance as the route callback, the closure’s state is bound to the `Container` instance. This means you will have access to the DI container instance inside of the Closure via the `$this` keyword:
```php
$router->get('/hello/{name}', function (Request $request, Response $response, string $name) {
    $myservice = $this->get('myservice');
    //...
});
```

#### Access to current route
If you get the route's object, you can it using the request attribute:
```php
$router->get('/hello/{name}', function (Request $request, Response $response, string $name) {
    $route = $request->getAttribute('route');
    echo $route->getUri(); // /hello/name
});
```

### Placeholders
Route patterns may use named placeholders to dynamically match HTTP request URI segments.

#### Format
A route pattern placeholder starts with a `{`, followed by the placeholder name, ending with a `}`. Name and value placeholder may be each character from a-z, A-Z, 0-9, including the _ (underscore).
```php
$router->get('/hello/{name}', function (Request $request, Response $response, string $name) {
    echo $name;
});
```

#### Optional
To make a placeholder optional, wrap it in square brackets:
```php
$router->get('/hello[/{name}]', function (Request $request, Response $response, string $name = null) {
    if ($name) {
        echo $name;
    }
    //...
});
```
You can use a multiple optional parameters:
```php
$router->get('/blog[/{year}][/{month}][/{day}]', function(Request $request, Response $response, int $year = 2018, int $month = 12, int $day = 31){
    $response->getBody()->write('Blog! Year: '.$year.', Month: '.$month.', Day: '.$day);
    return $response;
});
```
For "simple slug" optional parameters, you can do this:
```php
$router->get('/blog[/{year}][/{slug}]', function(Request $request, Response $response, int $year = 2018, string $slug = null){
    //...
})->where('slug', '[\/\w\-]+');
```
In this example, a URI of /`blog/2018/my-post-title` would result in the `$year` (2018) and `$slug` (my-post-title) arguments.

#### Set regex route
By default the placeholders can accept any character allowed for composing an uri with the exception of the `/` character. However, placeholders can also require the HTTP request URI to match a particular regular expression. For this, you can use `where()` method:
```php
$router->get('/blog/{id}/{name}', function(Request $request, Response $response, int $id, string $name){
    //...
})->where([
    'id'   => '[0-9]+',
    'name' => '[a-z]+',
]);
```

### Set name route
You can be assigned a name at the route with `name()` method:
```php
$router->get('/hello/{name}', function (Request $request, Response $response, string $name) {
    //...
})->name('route');
```
### Create route groups
You can organize routes into logical groups with `group()` method. If you want add a route prefix you can use `prefix()` method:
```php
$router->prefix('/api')->group(function($router) {
    $router->get('/user/{id}', function(Request $request, Response $response, int $id) {
        //...
    });
});
```
In this example URI is, for example, /api/user/1.

### Add middleware to route
You can also attach a PSR-15 middleware to any route or route group.

#### Route middleware
You can use the `middleware()` method to assign one or more middleware at the route:
```php
$router->get('/users', function(Request $request, Response $response) {
    //...
})->middleware('App\TestMiddleware1', 'App\TestMiddleware2');
```

#### Group middleware
In addition to the routes, you can assign one or more middleware to a group and to individual routes within the group:
```php
$router->prefix('/api')->middleware('App\GroupMiddlewareTest')->group(function($router) {
    $router->get('/user/{id}', function(Request $request, Response $response, int $id){
        //...
    })->middleware('App\RouteMiddlewareTest');
});
```
### Resolve via Container
In callback, in addition to closure, you can use the `class@method` string or `['className', 'methodName']` array:
```php
// string
$router->get('/user/{id}', 'user@getById');

// array
use Path\To\User;

$router->get('/user/{id}', [User::class, 'geById']);
```
It translates into a class/method call like this:
```php
use Embryo\Routing\Controller;
use Psr\Http\Message\ResponseInterface;

class User extends Controller
{
    public function getById(int $id): ResponseInterface
    {
        $this->response->getBody()->write('The User id is: '.$id);
        return $this->response;
    }
}
```
In this example you will have access to the DI container instance inside of the class via the `$this` keyword. 
If you want have access to service in container use `$this->get('myservice')`. 
If you want have access to the request or response instance use `$this->request` and `$this->response`.
In addition, methods parameters it's may also called using `type-hinting`, which means you can put the class you need in the method and the Service Container will resolve it automatically:
```php
use Path\To\MyService;
use Embryo\Routing\Controller;
use Psr\Http\Message\ResponseInterface;

class User extends Controller
{
    public function getById(MyService $service, int $id): ResponseInterface
    {
        //...
    }
}
```

### Set default namespace
You may to set default namespace for your controllers with `setNamespace()` method:
```php
$router = new Router;
$router->setNamespace('App\\Controller');

$router->get('/', 'PageController@index'); // App\Controller\PageController

//...
```

### Working in subfolder
Embryo Routing can works in a subdirectory by setting the path with `setBasePath()` method:
```php
$router = new Router;
$router->setBasePath('/path/subdirectory');

$router->get('/', function(Request $request, Response $response) {
    $response->getBody()->write('Hello World!');
    return $response;
});

//...
```