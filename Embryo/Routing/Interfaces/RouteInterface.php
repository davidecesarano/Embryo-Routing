<?php 

    /**
     * RouteInterface
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing 
     */
    
    namespace Embryo\Routing\Interfaces;

    interface RouteInterface
    {
        /**
         * @param string $basePath 
         * @return RouteInterface
         */
        public function withBasePath(string $basePath): RouteInterface;
        
        /**
         * @return string
         */
        public function getBasePath(): string;
        
        /**
         * @param array $prefix 
         * @return RouteInterface
         */
        public function withPrefix(array $prefix): RouteInterface;
        
        /**
         * @return string
         */
        public function getPrefix(): string;
        
        /**
         * @param string $namespace 
         * @return RouteInterface
         */
        public function withNamespace(string $namespace): RouteInterface;
        
        /**
         * @return string
         */
        public function getNamespace(): string;
        
        /**
         * @param array $middleware 
         * @return RouteInterface
         */
        public function withMiddleware(array $middleware): RouteInterface;
        
        /**
         * @return array
         */
        public function getMiddleware(): array;
        
        /**
         * @param array $methods 
         * @return RouteInterface
         */
        public function withMethods(array $methods): RouteInterface;
        
        /**
         * @return array
         */
        public function getMethods(): array;
        
        /**
         * @param string $pattern 
         * @return RouteInterface
         */
        public function withPattern(string $pattern): RouteInterface;
        
        /**
         * @return string
         */
        public function getPattern(): string;
        
        /**
         * @param string|array|\Closure $callback 
         * @return RouteInterface
         */
        public function withCallback($callback): RouteInterface;
        
        /**
         * @return string|array|\Closure
         */
        public function getCallback();
        
        /**
         * @return string
         */
        public function getRoutePath(): string;
        
        /**
         * @return string
         */
        public function getName(): string; 
        
        /**
         * @return string
         */
        public function getUri(): string;     
        
        /**
         * @return array
         */
        public function getArguments(): array;  
        
        /**
         * @return int
         */
        public function getStatus(): int;      
        
        /**
         * @param string $uri 
         * @param string $method 
         * @return bool
         */
        public function match(string $uri, string $method): bool;

        /**
         * @param string|array|\Psr\Http\Server\MiddlewareInterface $middleware
         * @return RouteInterface 
         */
        public function middleware($middleware): RouteInterface;

        /**
         * @param string|array $name 
         * @param string|null $regex
         * @return RouteInterface 
         */
        public function where($name, $regex = null): RouteInterface;

        /**
         * @param string $name
         * @return RouteInterface
         */
        public function name(string $name): RouteInterface;
    }