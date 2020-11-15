<?php 

    require __DIR__.'/../vendor/autoload.php';

    use Embryo\Container\ContainerBuilder;
    use Embryo\Http\Emitter\Emitter;
    use Embryo\Http\Factory\ServerRequestFactory;
    use Embryo\Http\Factory\ResponseFactory;
    use Embryo\Http\Server\RequestHandler;
    use Embryo\Routing\Router;

    $container      = new ContainerBuilder;
    $request        = (new ServerRequestFactory)->createServerRequestFromServer();
    $response       = (new ResponseFactory)->createResponse(200);
    $requestHandler = new RequestHandler;
    $emitter        = new Emitter;
    $router         = new Router;
    
    $router->get('/', function($request, $response){
        return $response->write('Hello World!');
    });

    $router->get('/example', function($request, $response){
        return $response->write('Example!');
    });

    $router->get('/blog[/{year}][/{month}][/{day}]', function($request, $response, $year = 2018, $month = 12, $day = 31){
        return $response->write('Blog! Year: '.$year.', Month: '.$month.', Day: '.$day);
    });

    $requestHandler->add(new Embryo\Routing\Middleware\MethodOverrideMiddleware);
    $requestHandler->add(new Embryo\Routing\Middleware\RoutingMiddleware($router));
    $requestHandler->add(new Embryo\Routing\Middleware\RequestHandlerMiddleware($container));
    
    $response = $requestHandler->dispatch($request, $response);
    $emitter->emit($response);