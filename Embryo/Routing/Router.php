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
    use Psr\Http\Server\MiddlewareInterface;
    
    class Router implements RouterInterface
    {    
        /**
         * @var array $routes
         */
        private $routes = [];

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
         * Set base path.
         * 
         * @param string $basePath
         * @return RouterInterface
         */
        public function setBasePath(string $basePath): RouterInterface
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
         * @return self
         */
        public function get(string $pattern, $callback): self
        {
            return $this->add(['GET'], $pattern, $callback);
        }

        /**
         * POST
         *
         * @param string $pattern
         * @param mixed $callback
         * @return self
         */
        public function post(string $pattern, $callback): self
        {
            return $this->add(['POST'], $pattern, $callback);
        }
        
        /**
         * PUT
         *
         * @param string $pattern
         * @param mixed $callback
         * @return self
         */
        public function put(string $pattern, $callback): self
        {
            return $this->add(['PUT'], $pattern, $callback);
        }

        /**
         * PATCH
         *
         * @param string $pattern
         * @param mixed $callback
         * @return self
         */
        public function patch(string $pattern, $callback): self
        {
            return $this->add(['PATCH'], $pattern, $callback);
        }
        
        /**
         * DELETE
         *
         * @param string $pattern
         * @param mixed $callback
         * @return self
         */
        public function delete(string $pattern, $callback): self
        {
            return $this->add(['DELETE'], $pattern, $callback);
        }

        /**
         * OPTIONS
         *
         * @param string $pattern
         * @param mixed $callback
         * @return self
         */
        public function options(string $pattern, $callback): self
        {
            return $this->add(['OPTIONS'], $pattern, $callback);
        }

        /**
         * Map route with specific HTTP methods.
         *
         * @param array $methdos
         * @param string $pattern
         * @param mixed $callback
         * @return self
         */
        public function map(array $methods, string $pattern, $callback): self
        {
            return $this->add($methods, $pattern, $callback);
        }
        
        /**
         * Create route with all HTTP methods.
         *
         * @param string $pattern
         * @param mixed $callback
         * @return self
         */
        public function all(string $pattern, $callback): self
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
         * @return self
         */
        private function add(array $methods, string $pattern, $callback): self
        {
            foreach ($methods as $method) {
                $route = new Route;
                $route = $route->withBasePath($this->basePath);
                $route = $route->withPrefix($this->prefix);
                $route = $route->withNamespace($this->namespace);
                $route = $route->withMiddleware($this->middleware);
                $route = $route->withMethod($method);
                $route = $route->withPattern($pattern);
                $route = $route->withCallback($callback);
                $this->routes[] = $route;
            }
            return $this;
        }

        /** 
         * Dispatch routes.
         * 
         * Find the route and return
         * a route object if find it.
         * 
         * @param ServerRequestInterface $request
         * @return RouteInterface|bool
         */
        public function dispatch(ServerRequestInterface $request)
        {
            $path   = $request->getUri()->getPath();
            $method = $request->getMethod();
            $uri    = filter_var($path, FILTER_SANITIZE_URL);
            $routes = [];

            foreach ($this->routes as $route) {
                if ($route->match($uri, $method)) {
                    $routes[$route->getMethod()] = $route;
                }
            }

            if (empty($routes)) return false;
            if (isset($routes[$method])) return $routes[$method];
            return reset($routes);
        }
    }