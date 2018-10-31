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

    use Embryo\Routing\Interfaces\{RouteInterface, RouterInterface};
    use Embryo\Routing\Route;
    use Psr\Http\Message\ServerRequestInterface;
    
    class Router implements RouterInterface
    {          
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
         * @var string $prefix
         */
        private $prefix = '';
        
        /**
         * @var array $middleware
         */
        private $middleware = []; 

        /**
         * Sets base path.
         * 
         * @param string $basePath
         * @return self
         */
        public function setBasePath($basePath)
        {
            $this->basePath = $basePath;
            return $this;
        }

        /**
         * Sets namespace for controller.
         * 
         * @param string $namespace
         * @return self
         */
        public function setNamespace(string $namespace)
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
        public function get($pattern, $callback): RouteInterface
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
        public function post($pattern, $callback): RouteInterface
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
        public function put($pattern, $callback): RouteInterface
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
        public function patch($pattern, $callback): RouteInterface
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
        public function delete($pattern, $callback): RouteInterface
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
        public function options($pattern, $callback): RouteInterface
        {
            return $this->add(['OPTIONS'], $pattern, $callback);
        }

        /**
         * Maps route with specific HTTP methods.
         *
         * @param array $methdos
         * @param string $pattern
         * @param mixed $callback
         * @return RouteInterface
         */
        public function map(array $methods, $pattern, $callback): RouteInterface
        {
            return $this->add($methods, $pattern, $callback);
        }
        
        /**
         * Creates route with all HTTP methods.
         *
         * @param string $pattern
         * @param mixed $callback
         * @return RouteInterface
         */
        public function all($pattern, $callback): RouteInterface
        {
            return $this->add(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $pattern, $callback);            
        }

        /**
         * Sets one or more middleware for group routes.
         *
         * @param array $middleware
         * @return self
         */
        public function middleware(array $middleware)
        {
            $this->middleware = $middleware;
            return $this;
        }

        /**
         * Sets prefix for group routes.
         *
         * @param string $prefix
         * @return self
         */
        public function prefix(string $prefix)
        {
            $this->prefix = $prefix;
            return $this;
        }

        /**
         * Creates routes into logical group.
         *
         * @param callable $callback
         * @return self
         */
        public function group(callable $callback)
        {
            call_user_func($callback, $this);
            $this->prefix = '';
            $this->middleware = [];
            return $this;
        }

        /**
         * Creates a route that redirect GET HTTP request to a 
         * different url. 
         *
         * @param string $pattern
         * @param string $location
         * @param integer $code
         */
        public function redirect(string $pattern, string $location, int $code = 302)
        {
            $this->add(['GET'], $pattern, function($request, $response) use($location, $code){
                $response = $response->withStatus($code);
                return $response->withHeader('Location', $location);
            });
        }

        /**
         * Creates route.
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
         * Dispatcher.
         * 
         * Finds the route, starts dispatcher and returns
         * a route object if find it.
         * 
         * @param ServerRequestInterface $request
         * @return RouteInterface|bool
         */
        public function dispatcher(ServerRequestInterface $request)
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
    }