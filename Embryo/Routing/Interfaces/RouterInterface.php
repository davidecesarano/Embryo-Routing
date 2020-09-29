<?php 

    /**
     * RouterInterface
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing 
     */
    
    namespace Embryo\Routing\Interfaces;

    use Embryo\Routing\Interfaces\RouteInterface;
    use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

    interface RouterInterface
    {
        /**
         * setBasePath
         * 
         * @param string $basePath  
         * @return RouterInterface
         */
        public function setBasePath(string $basePath): RouterInterface;
        
        /**
         * setNamespace
         * 
         * @param string $namespace  
         * @return RouterInterface
         */
        public function setNamespace(string $namespace): RouterInterface;
        
        /**
         * get
         * 
         * @param string $pattern 
         * @param mixed $callback 
         * @return RouteInterface
         */
        public function get(string $pattern, $callback): RouteInterface;
        
        /**
         * post
         * 
         * @param string $pattern 
         * @param mixed $callback 
         * @return RouteInterface
         */
        public function post(string $pattern, $callback): RouteInterface;
        
        /**
         * put
         * 
         * @param string $pattern 
         * @param mixed $callback 
         * @return RouteInterface
         */
        public function put(string $pattern, $callback): RouteInterface;
        
        /**
         * patch
         * 
         * @param string $pattern 
         * @param mixed $callback 
         * @return RouteInterface
         */
        public function patch(string $pattern, $callback): RouteInterface;
        
        /**
         * delete
         * 
         * @param string $pattern 
         * @param mixed $callback 
         * @return RouteInterface
         */
        public function delete(string $pattern, $callback): RouteInterface;
        
        /**
         * options
         * 
         * @param string $pattern 
         * @param mixed $callback 
         * @return RouteInterface
         */
        public function options(string $pattern, $callback): RouteInterface;
        
        /**
         * map
         * 
         * @param array $methods
         * @param string $pattern 
         * @param mixed $callback 
         * @return RouteInterface
         */
        public function map(array $methods, string $pattern, $callback): RouteInterface;
        
        /**
         * all
         * 
         * @param string $pattern 
         * @param mixed $callback 
         * @return RouteInterface
         */
        public function all(string $pattern, $callback): RouteInterface;
        
        /**
         * middleware
         * 
         * @param mixed $middleware
         * @return RouterInterface
         */
        public function middleware($middleware): RouterInterface;
        
        /**
         * prefix
         * 
         * @param string $prefix 
         * @return RouterInterface
         */
        public function prefix(string $prefix): RouterInterface;
        
        /**
         * group
         * 
         * @param callable $callback 
         * @return RouterInterface
         */
        public function group(callable $callback): RouterInterface;
        
        /**
         * dispatch
         * 
         * @param ServerRequestInterface $request 
         * @return mixed
         */
        public function dispatch(ServerRequestInterface $request);
    }