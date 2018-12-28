# Embryo Routing
A lightweight, fast and PSR compatible PHP Router.

## Features
* PSR (7, 11, 15) compatible.
* Static, dynamic and optional route patterns.
* Supports GET, POST, PUT, PATCH, DELETE and OPTIONS request methods.
* Before and after route middlewares.
* Supports grouping routes.
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
$ cd example
$ php -S localhost:8000
```
Going to http://localhost:8000 will now display "Hello World!".

## Usage
* [Create routes](#create-routes)
* [Callbacks](#callbacks)
* [Placeholders](#placeholders)
* [Set name route](#set-name-route)
* [Create route groups](#create-route-group)
* [Add middleware to route](#add-middleware-to-route)
* [Resolve via Container](#resolve-via-container)
* [Working in subfolder](#working-in-subdirectory)

### Create routes
You can define application routes using methods on the Router object. Every method accepts two arguments:
* The route pattern (with optional placeholders)
* The route callback (a closure or a `class@method` string)
```php
// GET Route
$router->get('/blog/{id}', function($request, $response, $id) {
    return $response->write('This is post with id '.$id);
}
```

#### Methods
Embryo Routing supports GET, POST, PUT, PATCH, DELETE and OPTIONS request methods. Every request method corresponds to a method of the Router object: `get()`, `post()`, `put()`, `patch()`, `delete()` and `options()`.
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
$router->get('/hello/{name}', function ($request, $response, $name) {
    echo $name;
});

// response object
$router->get('/hello/{name}', function ($request, $response, $name) {
    return $response->write($name);
});

// json
$router->get('/hello/{name}', function ($request, $response, $name) {
    return [
        'name' => $name
    ];
});
```

#### Closure binding
If you use a Closure instance as the route callback, the closure’s state is bound to the `Container` instance. This means you will have access to the DI container instance inside of the Closure via the `$this` keyword:
```php
$router->get('/hello/{name}', function ($request, $response, $name) {
    $this->get('session')->set('name', $name);
});
```

### Placeholders
Route patterns may use named placeholders to dynamically match HTTP request URI segments.

#### Format
A route pattern placeholder starts with a `{`, followed by the placeholder name, ending with a `}`. Name and value placeholder may be each character from a-z, A-Z, 0-9, including the _ (underscore).
```php
$router->get('/hello/{name}', function ($request, $response, $name) {
    echo $name;
});
```

#### Optional
To make a placeholder optional, wrap it in square brackets:
```php
$router->get('/hello[/{name}]', function ($request, $response, $name = null) {
    if ($name) {
        echo $name;
    }
});
```
You can use a multiple optional parameters:
```php
$router->get('/blog[/{year}][/{month}][/{day}]', function($request, $response, $year = 2018, $month = 12, $day = 31){
    return $response->write('Blog! Year: '.$year.', Month: '.$month.', Day: '.$day);
});
```
For "slug" optional parameters, you can do this:
```php
$router->get('/blog[/{year}][/{slug}]', function($request, $response, $year = 2018, $slug = null){
    //...
})->where('slug', '[\/\w\-]+');
```
In this example, a URI of /`blog/2018/my-post-title` would result in the `$year` (2018) and `$slug` (my-post-title) arguments.

#### Set regex route
By default the placeholders can accept any character from a-z, A-Z, 0-9, including the _ (underscore). However, placeholders can also require the HTTP request URI to match a particular regular expression. For this, you can use `where()` method:
```php
$router->get('/blog/{id}/{name}', function($request, $response, $id, $name){
    //...
})->where([
    'id'   => '[0-9]+',
    'name' => '[a-z]+',
]);
```

### Set name route
You can be assigned a name at the route with `name()` method:
```php
$router->get('/hello/{name}', function ($request, $response, $name) {
    echo $name;
})->name('route');
```
### Create route groups
You can organize routes into logical groups with `group()` method. If you want add a route prefix you can use `prefix()` method:
```php
$router->prefix('/api')->group(function($router){
    $router->get('/user/{id}', function($request, $response, $id){
        //...
    });
});
```
In this example URI is, for example, /api/user/1.

#### CRUD routes
The `crud()` method introduces the ability to write the group of crud routes:
```php
$router->crud('/users', 'User');
```
This code snippet is equal to:
```php
$router->get('/users', 'User@index');
$router->get('/users/create', 'User@create');
$router->post('/users', 'User@store');
$router->get('/users/{id}', 'User@show');
$router->get('/users/{id}/edit', 'User@edit');
$router->put('/users/{id}', 'User@update');
$router->delete('/users/{id}', 'User@destroy');
```

### Add middleware to route
You can also attach a PSR-15 middleware to any route or route group.

#### Route middleware
You can use the `middleware()` method to assign one or more middleware at the route:
```php
$router->get('/users', function($request, $response){
    //...
})->middleware('App\TestMiddleware1', 'App\TestMiddleware2');
```

#### Group middleware
In addition to the routes, you can assign one or more middleware to a group and to individual routes within the group:
```php
$router->prefix('/api')->middleware('App\GroupMiddlewareTest')->group(function($router){
    $router->get('/user/{id}', function($request, $response, $id){
        //...
    })->middleware('App\RouteMiddlewareTest');
});
```
### Resolve via Container
In callback, in addition to closure, you can use the `class@method` string:
```php
$router->get('/user/{id}', 'user@getById');
```
It translates a string entry into a class/method call like this:
```php
use Embryo\Routing\Controller;

class User extends Controller
{
    public function getById($id)
    {
        $this->get('session')->set('id', $id);
        //...
    }
}
```
In this example you will have access to the DI container instance inside of the class via the `$this` keyword. If you will have access to the request or response instance use `$this->request` and `$this->response`.

### Working in subfolder
Embryo Routing can works in a subdirectory by setting the path with `setBasePath()` method:
```php
$router = new Router;
$router->setBasePath('/path/subdirectory');

$router->get('/', function($request, $response){
    return $response->write('Hello World!');
});

//...
```