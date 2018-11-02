<?php 

    require __DIR__.'/../vendor/autoload.php';

    use Embryo\Http\Emitter\Emitter;
    use Embryo\Http\Factory\ServerRequestFactory;
    use Embryo\Http\Factory\ResponseFactory;
    use Embryo\Http\Server\MiddlewareDispatcher;
    use Embryo\Routing\Router;

    $container  = new Embryo\Container\Container;
    $request    = (new ServerRequestFactory)->createServerRequestFromServer();
    $response   = (new ResponseFactory)->createResponse(200);
    
    $router = new Router;

    $router->get('/', function($request, $response){
        return $response->write('Hello World!');
    });

    $router->get('/example', function($request, $response){
        return $response->write('Example!');
    });

    $router->get('/blog[/{year}][/{month}][/{day}]', function($request, $response, $year = 2018, $month = 12, $day = 31){
        return $response->write('Blog! Year: '.$year.', Month: '.$month.', Day: '.$day);
    });

    $middleware = new MiddlewareDispatcher;
    $middleware->add(new Embryo\Routing\Middleware\MethodOverrideMiddleware);
    $middleware->add(new Embryo\Routing\Middleware\RoutingMiddleware($router));
    $middleware->add(new Embryo\Routing\Middleware\RequestHandlerMiddleware($container));
    $response = $middleware->dispatch($request, $response);
    
    $emitter = new Emitter;
    $emitter->emit($response);