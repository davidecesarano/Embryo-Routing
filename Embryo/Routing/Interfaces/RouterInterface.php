<?php 

    /**
     * RouterInterface
     */
    
    namespace Embryo\Routing\Interfaces;

    use Psr\Http\Message\ServerRequestInterface;

    interface RouterInterface
    {
        public function setBasePath(string $basePath);
        public function setNamespace(string $namespace);
        public function get($pattern, $callback);
        public function post($pattern, $callback);
        public function put($pattern, $callback);
        public function patch($pattern, $callback);
        public function delete($pattern, $callback);
        public function options($pattern, $callback);
        public function map(array $methods, $pattern, $callback);
        public function all($pattern, $callback);
        public function middleware(array $middleware);
        public function prefix(string $prefix);
        public function group(callable $callback);
        public function dispatcher(ServerRequestInterface $request);
    }