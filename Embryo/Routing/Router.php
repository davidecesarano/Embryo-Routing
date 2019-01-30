<?php 
    
    /**
     * Router 
     *
     * It allows the creation of routes through which it is possible 
     * to invoke a closure (function) or a controller (class).
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing 
     */

    namespace Embryo\Routing;

    use Exception;
    use Embryo\Routing\Exceptions\{MethodNotAllowedException, NotFoundException};
    use Embryo\Routing\Interfaces\{RouteInterface, RouterInterface};
    use Embryo\Routing\Route;
    use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
    use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
    
    class Router implements RouterInterface
    {    
        /**
         * @var RequestHandlerInterface
         */
        private $requestHandler;

        /**
         * @var array $routes
         */
        private $routes = [
            'GET'     => [], 
            'POST'    => [],
            'PUT'     => [],
            'PATCH'   => [],
            'DELETE'  => [],
            'OPTIONS' => []
        ];

        /**
         * @var string $basePath
         */
        private $basePath = '/';
        
        /**
         * @var string $namespace
         */
        private $namespace = 'App\\Controllers';

        /**
         * @var array $prefix
         */
        private $prefix = [];
        
        /**
         * @var array $middleware
         */
        private $middleware = []; 

        /**
         * Set request handler.
         *
         * @param RequestHandlerInterface $requestHandler
         */
        public function __construct(RequestHandlerInterface $requestHandler)
        {
            $this->requestHandler = $requestHandler;
        }

        /**
         * Set base path.
         * 
         * @param string $basePath
         * @return RouterInterface
         */
        public function setBasePath($basePath): RouterInterface
        {
            $this->basePath = $basePath;
            return $this;
        }

        /**
         * Set namespace for controller.
         * 
         * @param string $namespace
         * @return RouterInterface
         */
        public function setNamespace(string $namespace): RouterInterface
        {
            $this->namespace = $namespace;
            return $this;
        }
        
        /**
         * GET
         *
         * @param string $pattern
         * @param mixed $callback
         * @return RouteInterface
         */
        public function get(string $pattern, $callback): RouteInterface
        {
            return $this->add(['GET'], $pattern, $callback);
        }

        /**
         * POST
         *
         * @param string $pattern
         * @param mixed $callback
         * @return RouteInterface
         */
        public function post(string $pattern, $callback): RouteInterface
        {
            return $this->add(['POST'], $pattern, $callback);
        }
        
        /**
         * PUT
         *
         * @param string $pattern
         * @param mixed $callback
         * @return RouteInterface
         */
        public function put(string $pattern, $callback): RouteInterface
        {
            return $this->add(['PUT'], $pattern, $callback);
        }

        /**
         * PATCH
         *
         * @param string $pattern
         * @param mixed $callback
         * @return RouteInterface
         */
        public function patch(string $pattern, $callback): RouteInterface
        {
            return $this->add(['PATCH'], $pattern, $callback);
        }
        
        /**
         * DELETE
         *
         * @param string $pattern
         * @param mixed $callback
         * @return RouteInterface
         */
        public function delete(string $pattern, $callback): RouteInterface
        {
            return $this->add(['DELETE'], $pattern, $callback);
        }

        /**
         * OPTIONS
         *
         * @param string $pattern
         * @param mixed $callback
         * @return RouteInterface
         */
        public function options(string $pattern, $callback): RouteInterface
        {
            return $this->add(['OPTIONS'], $pattern, $callback);
        }

        /**
         * Map route with specific HTTP methods.
         *
         * @param array $methdos
         * @param string $pattern
         * @param mixed $callback
         * @return RouteInterface
         */
        public function map(array $methods, string $pattern, $callback): RouteInterface
        {
            return $this->add($methods, $pattern, $callback);
        }
        
        /**
         * Create route with all HTTP methods.
         *
         * @param string $pattern
         * @param mixed $callback
         * @return RouteInterface
         */
        public function all(string $pattern, $callback): RouteInterface
        {
            return $this->add(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $pattern, $callback);            
        }

        /**
         * Create CRUD routes.
         *
         * @param string $pattern
         * @param string $class
         * @param string $regex
         * @return void
         */
        public function crud(string $pattern, string $class, string $regex = '{param}')
        {
            $this->get($pattern, $class.'@index');
            $this->get($pattern.'/create', $class.'@create');
            $this->post($pattern, $class.'@store');
            $this->get($pattern.'/'.$regex, $class.'@show');
            $this->get($pattern.'/'.$regex.'/edit', $class.'@edit');
            $this->put($pattern.'/'.$regex, $class.'@update');
            $this->delete($pattern.'/'.$regex, $class.'@destroy');
        }

        /**
         * Set one or more middleware for group routes.
         *
         * @param string|array|MiddlewareInterface $middleware
         * @return RouterInterface
         * @throws InvalidArgumentException
         */
        public function middleware($middleware): RouterInterface
        {
            if (!is_string($middleware) && !is_array($middleware) && !$middleware instanceof MiddlewareInterface) {
                throw new \InvalidArgumentException('Middleware must be a string, an array or an instance of MiddlewareInterface');
            }

            if (is_array($middleware)) {
                foreach ($middleware as $m) {
                    $this->middleware[] = $m;
                }    
            }

            $this->middleware[] = $middleware;
            return $this;
        }

        /**
         * Set prefix for group routes.
         *
         * @param string $prefix
         * @return RouterInterface
         */
        public function prefix(string $prefix): RouterInterface
        {
            $this->prefix[] = $prefix;
            return $this;
        }

        /**
         * Create routes into logical group.
         *
         * @param callable $callback
         * @return RouterInterface
         */
        public function group(callable $callback): RouterInterface
        {
            call_user_func($callback, $this);
            array_pop($this->prefix);
            array_pop($this->middleware);
            return $this;
        }

        /**
         * Create a route that redirect GET HTTP request to a 
         * different url. 
         *
         * @param string $pattern
         * @param string $location
         * @param int $code
         */
        public function redirect(string $pattern, string $location, int $code = 302)
        {
            $this->add(['GET'], $pattern, function($request, $response) use($location, $code){
                $response = $response->withStatus($code);
                return $response->withHeader('Location', $location);
            });
        }

        /**
         * Create route.
         *
         * @param array $methods
         * @param string $pattern
         * @param string|callable $callback
         * @return RouteInterface
         */
        private function add(array $methods, string $pattern, $callback): RouteInterface
        {
            $route = new Route;
            $route = $route->withBasePath($this->basePath);
            $route = $route->withPrefix($this->prefix);
            $route = $route->withNamespace($this->namespace);
            $route = $route->withMiddleware($this->middleware);
            $route = $route->withMethods($methods);
            $route = $route->withPattern($pattern);
            $route = $route->withCallback($callback);

            foreach ($methods as $method) {
                array_push($this->routes[$method], $route);
            }
            return $route;
        }
        
        /** 
         * Match route.
         * 
         * Find the route and return
         * a route object if find it.
         * 
         * @param ServerRequestInterface $request
         * @return RouteInterface|bool
         */
        public function match(ServerRequestInterface $request)
        {
            $path   = $request->getUri()->getPath();
            $method =  $request->getMethod();
            $uri    = filter_var($path, FILTER_SANITIZE_URL);
            foreach ($this->routes[$method] as $route) {
                if ($route->match($uri, $method)) {
                    return $route;
                }
            }
            return false;
        }
        
        /** 
         * Dispatching route.
         * 
         * @param ServerRequestInterface $request
         * @param ResponseInterface $response
         * @return RouteInterface|bool
         * @throws MethodNotAllowedException
         * @throws Exception
         * @throws NotFoundException
         */
        public function dispatch(ServerRequestInterface $request, ResponseInterface $response)
        {
            $route = $this->match($request);
            if ($route instanceof RouteInterface) {

                $status = $route->getStatus();
                switch($status) {
                    case 200:
                        return $this->handle($request, $response, $route);
                    case 405:
                        throw new MethodNotAllowedException('Method Not Allowed', 405);
                    default:
                        throw new Exception('Internal Server Error', 500);
                }
                
            } else {
                throw new NotFoundException('Not Found', 404);
            }
        }

        /**
         * Route handler.
         *
         * @param ServerRequestInterface $request
         * @param ResponseInterface $response
         * @param RouteInterface $route
         * @return ResponseInterface
         */
        public function handle(ServerRequestInterface $request, ResponseInterface $response, RouteInterface $route): ResponseInterface 
        {
            foreach ($route->getMiddleware() as $middleware) {
                $this->requestHandler->add($middleware);
            }
            $request = $request->withAttribute('route', $route);
            return $this->requestHandler->dispatch($request, $response);
        }
    }