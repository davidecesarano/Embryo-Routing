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
        public function withBasePath(string $basePath): RouteInterface;
        public function getBasePath(): string;
        public function withPrefix(array $prefix): RouteInterface;
        public function getPrefix(): string;
        public function withNamespace(string $namespace): RouteInterface;
        public function getNamespace(): string;
        public function withMiddleware(array $middleware): RouteInterface;
        public function getMiddleware(): array;
        public function withMethods(array $methods): RouteInterface;
        public function getMethods(): array;
        public function getMethod(): string;
        public function withPattern(string $pattern): RouteInterface;
        public function getPattern(): string;
        public function withCallback($callback): RouteInterface;
        public function getCallback();
        public function getRoutePath(): string;
        public function getName(): string; 
        public function getUri(): string;     
        public function getArguments(): array;  
        public function getStatus(): int;      
        public function match(string $uri, string $method): bool;
    }