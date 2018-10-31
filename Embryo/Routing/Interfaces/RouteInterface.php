<?php 

    /**
     * RouteInterface
     */
    
    namespace Embryo\Routing\Interfaces;

    interface RouteInterface
    {
        public function withBasePath(string $basePath);
        public function getBasePath();
        public function withPrefix(string $prefix);
        public function getPrefix();
        public function withNamespace(string $namespace);
        public function getNamespace();
        public function withMiddleware(array $middleware);
        public function middleware(...$middleware);
        public function getMiddleware();
        public function withMethods(array $methods);
        public function getMethods();
        public function getMethod();
        public function withPattern(string $pattern);
        public function getPattern();
        public function withCallback($callback);
        public function getCallback();
        public function setRoutePath(string $path);
        public function getRoutePath();
        public function where($name, $regex);        
        public function getArguments();
        public function name(string $name);
        public function getName();
        public function match(string $uri, string $method);
    }