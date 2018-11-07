<?php 
    
    /**
     * Route
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing  
     */

    namespace Embryo\Routing;
    
    use Embryo\Routing\Interfaces\RouteInterface;
    use Embryo\Routing\Traits\RouteActionTrait;
    use Embryo\Routing\Traits\RouteMatchTrait;
    use Embryo\Routing\Traits\RouteStatusTrait;
    use Psr\Container\ContainerInterface;
    
    class Route implements RouteInterface
    {    
        use RouteMatchTrait, RouteActionTrait, RouteStatusTrait;

        /**
         * @var string $basePath
         */
        private $basePath = '/';

        /**
         * @var string $prefix 
         */
        private $prefix = '';

        /**
         * @var string $namespace 
         */
        private $namespace;
        
        /**
         * @var string $method
         */
        protected $method;

        /**
         * @var array $methods 
         */
        private $methods = [];
        
        /**
         * @var string $pattern 
         */
        private $pattern;
        
        /**
         * @var string|callable $callback 
         */
        private $callback;

        /**
         * @var string $path
         */
        protected $routePath = '';

        /**
         * @var array $arguments 
         */
        protected $arguments = [];

        /**
         * @var string $uri
         */
        protected $uri;

        /**
         * @var array $middleware 
         */
        protected $middleware = [];

        /**
         * @var string $name
         */
        protected $name;

        /**
         * @var int $status
         */
        protected $status;

        /**
         * ------------------------------------------------------------
         * BASE PATH
         * ------------------------------------------------------------
         */

        /**
         * Returns an instance with the specified base path.
         * 
         * Default value is '/'. 
         * 
         * @param string $basePath 
         * @return RouteInterface
         */
        public function withBasePath(string $basePath): RouteInterface
        {
            $clone = clone $this;
            $clone->basePath = $basePath;
            return $clone;
        }

        /**
         * Returns base path.
         * 
         * @return string
         */
        public function getBasePath(): string
        {
            return $this->basePath;
        }

        /**
         * ------------------------------------------------------------
         * PREFIX
         * ------------------------------------------------------------
         */

        /**
         * Returns an instance with the specified prefix.
         * 
         * Default value is empty string.
         * 
         * @param string $prefix 
         * @return RouteInterface
         */
        public function withPrefix(string $prefix): RouteInterface
        {
            $clone = clone $this;
            $clone->prefix = $prefix;
            return $clone;
        }

        /**
         * Returns prefix.
         *
         * @return string
         */
        public function getPrefix(): string
        {
            return $this->prefix;
        }

        /**
         * ------------------------------------------------------------
         * NAMESPACE
         * ------------------------------------------------------------
         */

        /**
         * Returns an instance with the specified namespace.
         * 
         * @param string $namespace 
         * @return RouteInterface
         */
        public function withNamespace(string $namespace): RouteInterface
        {
            $clone = clone $this;
            $clone->namespace = $namespace;
            return $clone;
        }

        /**
         * Restituisce il namespace della rotta
         *
         * @return string
         */
        public function getNamespace(): string
        {
            return $this->namespace;
        }

        /**
         * ------------------------------------------------------------
         * MIDDLEWARE
         * ------------------------------------------------------------
         */

        /**
         * Returns an instance with the specified middleware
         * for group route.
         * 
         * @param array $middleware 
         * @return RouteInterface
         */
        public function withMiddleware(array $middleware): RouteInterface
        {
            $clone = clone $this;
            $clone->middleware = $middleware;
            return $clone;
        }

        /**
         * Returns middleware.
         *
         * @return array
         */
        public function getMiddleware(): array
        {
            return $this->middleware;
        }

        /**
         * ------------------------------------------------------------
         * METHODS
         * ------------------------------------------------------------
         */
        
        /**
         * Returns an instance with the specified methods.
         *
         * @param array $methods
         * @return RouteInterface
         */
        public function withMethods(array $methods): RouteInterface
        {
            $clone = clone $this;
            $clone->methods = $methods;
            return $clone;
        }

        /**
         * Returns methods.
         * 
         * @return array
         */
        public function getMethods(): array
        {
            return $this->methods;
        }

        /**
         * Returns route method.
         * 
         * @return string
         */
        public function getMethod(): string
        {
            return $this->method;
        }

        /**
         * ------------------------------------------------------------
         * PATTERN
         * ------------------------------------------------------------
         */

        /**
         * Returns an instance with the specified pattern route.
         *
         * @param string $pattern
         * @return RouteInterface
         */
        public function withPattern(string $pattern): RouteInterface
        {
            $clone = clone $this;
            $clone->pattern = $pattern;
            return $clone;
        }

        /**
         * Returns pattern route.
         * 
         * @return string
         */
        public function getPattern(): string
        {
            return $this->pattern;
        }
        
        /**
         * ------------------------------------------------------------
         * CALLBACK
         * ------------------------------------------------------------
         */

        /**
         * Returns an instance with the specified callback.
         *
         * @param string|callable $callback
         * @return RouteInterface
         * @throws InvalidArgumentException
         */
        public function withCallback($callback): RouteInterface
        {
            if (!is_string($callback) && !is_callable($callback)) {
                throw new \InvalidArgumentException('The callback route must be a string!');
            }

            $clone = clone $this;
            $clone->callback = $callback;
            return $clone;
        }

        /**
         * Returns callback.
         * 
         * @return string|callable
         */
        public function getCallback()
        {
            return $this->callback;
        }

        /**
         * ------------------------------------------------------------
         * ROUTE PATH
         * ------------------------------------------------------------
         */

        /**
         * Returns route path.
         *
         * @return string
         */
        public function getRoutePath(): string
        {
            return $this->routePath;
        }

        /**
         * ------------------------------------------------------------
         * NAME
         * ------------------------------------------------------------
         */

        /**
         * Returns name.
         * 
         * @return string
         */
        public function getName(): string
        {
            return $this->name;
        }
        
        /**
         * ------------------------------------------------------------
         * URI
         * ------------------------------------------------------------
         */

        public function getUri(): string
        {
            return $this->uri;
        }

        /**
         * ------------------------------------------------------------
         * ARGUMENTS
         * ------------------------------------------------------------
         */

        /**
         * Return arguments.
         * 
         * @return array
         */
        public function getArguments(): array
        {
            return $this->arguments;
        }

        /**
         * ------------------------------------------------------------
         * STATUS
         * ------------------------------------------------------------
         */

        /**
         * Return status code.
         * 
         * @return int
         */
        public function getStatus(): int
        {
            return $this->status;
        }

        /**
         * ------------------------------------------------------------
         * MATCH
         * ------------------------------------------------------------
         */

        /**
         * Match route from uri and http request method.
         * 
         * If methdo does not exists return false.
         * 
         * @param string $uri 
         * @param string $method 
         * @return bool
         */
        public function match(string $uri, string $method): bool
        {
            $uri     = rtrim($uri, '/');
            $path    = $this->composeRoutePath();
            $pattern = $this->getRouteRegexPath($path);

            if (preg_match('#^'.$pattern.'$#i', $uri, $arguments)) {

                if (!in_array($method, $this->methods)) {
                    $this->setStatus(405);
                }

                $this->setUri($uri);
                $this->setMethod($method);
                $this->setRoutePath($path);
                $this->setArguments($arguments, $path);
                $this->setStatus(200);
                return true;

            } else {
                return false;
            }
        }
    }