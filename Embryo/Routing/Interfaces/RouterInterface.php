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
        public function setBasePath(string $basePath): RouterInterface;
        public function setNamespace(string $namespace): RouterInterface;
        public function get(string $pattern, $callback);
        public function post(string $pattern, $callback);
        public function put(string $pattern, $callback);
        public function patch(string $pattern, $callback);
        public function delete(string $pattern, $callback);
        public function options(string $pattern, $callback);
        public function map(array $methods, string $pattern, $callback);
        public function all(string $pattern, $callback);
        public function middleware($middleware): RouterInterface;
        public function prefix(string $prefix): RouterInterface;
        public function group(callable $callback): RouterInterface;
        public function redirect(string $pattern, string $location, int $code);
        public function match(ServerRequestInterface $request);
        public function handle(ServerRequestInterface $request, ResponseInterface $response, RouteInterface $route): ResponseInterface;
    }