<?php 
    
    /**
     * Route
     * 
     * 
     */

    namespace Embryo\Routing;
    
    use Embryo\Routing\Interfaces\RouteInterface;
    use Psr\Container\ContainerInterface;
    
    class Route implements RouteInterface
    {    
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
        private $method;

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
        private $routePath = '';

        /**
         * @var array $arguments 
         */
        private $arguments = [];

        /**
         * @var string $uri
         */
        private $uri;

        /**
         * @var array $middleware 
         */
        private $middleware = [];

        /**
         * @var string $name
         */
        private $name;

        /**
         * ------------------------------------------------------------
         * BASEPATH
         * ------------------------------------------------------------
         */

        /**
         * Returns an instance with the specified base path.
         * 
         * Default value is '/'. 
         * 
         * @param string $basePath 
         * @return self
         */
        public function withBasePath(string $basePath)
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
        public function getBasePath()
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
         * @return self
         */
        public function withPrefix(string $prefix)
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
        public function getPrefix()
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
         * @return self
         */
        public function withNamespace(string $namespace)
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
        public function getNamespace()
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
         * @return self
         */
        public function withMiddleware(array $middleware)
        {
            $clone = clone $this;
            $clone->middleware = $middleware;
            return $clone;
        }

        /**
         * Sets one or more middleware for specific route.
         *
         * @param array $middleware
         * @return self 
         */
        public function middleware(...$middleware)
        {
            $this->middleware = array_merge($this->middleware, $middleware);
            return $this;
        }

        /**
         * Returns middleware.
         *
         * @return array
         */
        public function getMiddleware()
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
         * @return self
         */
        public function withMethods(array $methods)
        {
            $clone = clone $this;
            $clone->methods = $methods;
            return $clone;
        }

        /**
         * Sets method.
         * 
         * @param string $method
         * @return self
         */
        private function setMethod(string $method)
        {
            $this->method = $method;
            return $this;
        }

        /**
         * Returns methods.
         * 
         * @return array
         */
        public function getMethods()
        {
            return $this->methods;
        }

        /**
         * Returns route method.
         * 
         * @return string
         */
        public function getMethod()
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
         * @return self
         */
        public function withPattern(string $pattern)
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
        public function getPattern()
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
         * @return self
         */
        public function withCallback($callback)
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
         * PATH
         * ------------------------------------------------------------
         */

        /**
         * Sets route path.
         *
         * @param string $routePath
         * @return self
         */
        public function setRoutePath(string $routePath)
        {
            $this->routePath = $routePath;
            return $this;
        }

        /**
         * Returns route path.
         *
         * @return string
         */
        public function getRoutePath()
        {
            return $this->routePath;
        }

        /**
         * Composes route path.
         * 
         * This method combines the basePath, prefix and route pattern
         * for the composition of the route path.
         * 
         * @return string
         */
        private function composeRoutePath()
        {
            $basePath  = $this->getBasePath();
            $prefix    = $this->getPrefix();
            $pattern   = $this->getPattern();

            $basePath  = ($basePath === '/') ? '' : $basePath;
            $routePath = ($prefix !== '') ? $basePath.$prefix.$pattern : $basePath.$pattern;
            $routePath = rtrim($routePath, '/');   
            return $routePath;
        }

        /**
         * ------------------------------------------------------------
         * ARGUMENTS
         * ------------------------------------------------------------
         */

        /**
         * Sets specific regex for arguments.
         *
         * @param string|array $name 
         * @param string|null $regex
         * @return self 
         */
        public function where($name, $regex = null)
        {
            if (is_string($name) && is_string($regex)) {
                $this->arguments[$name] = $regex;
            }

            if (is_array($name)) {
                foreach ($name as $key => $value) {
                    $this->arguments[$key] = $value;
                }
            }
            return $this;
        }

        /**
         * Sets arguments.
         *
         * @param array $params
         * @param string $path
         * @return void
         */
        private function setArguments(array $params, string $path)
        {
            $params = array_slice($params, 1);
            if (!empty($params)) {

                preg_match_all('#({\w+}|\[\/{\w+}\])#', $path, $names);

                $keys = [];
                foreach($names[0] as $name) {
                    $name = str_replace('{', '', $name);
                    $name = str_replace('}', '', $name);
                    $name = str_replace('?', '', $name);
                    $name = str_replace('/', '', $name);
                    $name = str_replace('[', '', $name);
                    $name = str_replace(']', '', $name);
                    $keys[] = $name;
                }
                
                $values = [];
                foreach ($keys as $k => $v) {
                    $values[$k] = (!isset($params[$k])) ? null : ltrim($params[$k], '/');
                }

                $this->arguments = array_combine($keys, $values);
                return $this;

            }
        }

        /**
         * Return arguments.
         * 
         * @return array
         */
        public function getArguments()
        {
            return $this->arguments;
        }

        /**
         * ------------------------------------------------------------
         * NAME
         * ------------------------------------------------------------
         */

        /**
         * Sets name of specific route.
         *
         * @param string $name
         * @return self
         */
        public function name(string $name)
        {
            $this->name = $name;
            return $this;
        }

        /**
         * Returns name.
         * 
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * ------------------------------------------------------------
         * URI
         * ------------------------------------------------------------
         */

        /**
         * Sets uri.
         *
         * @param string $uri
         * @return self
         */
        private function setUri(string $uri)
        {
            $this->uri = $uri;
            return $this;
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
        public function match(string $uri, string $method)
        {
            if (!in_array($method, $this->methods)) {
                return false;
            }

            $uri     = rtrim($uri, '/');
            $path    = $this->composeRoutePath();
            $pattern = $this->getRouteRegexPath($path);

            if (preg_match('#^'.$pattern.'$#i', $uri, $arguments)) {

                $this->setUri($uri);
                $this->setMethod($method);
                $this->setRoutePath($path);
                $this->setArguments($arguments, $path);
                return true;

            } else { 
                return false;
            }
        }

        /**
         * Returns the route regex path.
         * 
         * If route doesn not specify the arguments with where() method, 
         * the arguments array is empty and function return the
         * 'digit' regex (\w+).
         * 
         * @param string $path
         * @return string
         * @throws InvalidArgumentException
         */
        private function getRouteRegexPath(string $path): string
        {
            $arguments = $this->getArguments();
            return preg_replace_callback('#({\w+}|\[\/{\w+}\])#', function($params) use($arguments){
                
                $name = str_replace('{', '', $params[0]);
                $name = str_replace('}', '', $name);
                $name = str_replace('?', '', $name);
                $name = str_replace('/', '', $name);
                $name = str_replace('[', '', $name);
                $name = str_replace(']', '', $name);
                
                if (preg_match('#\[\/{\w+}\]#', $params[0])) {
                    return '(\/[\w]+)?';
                } else if (preg_match('#{\w+}#', $params[0])) {
                    return (isset($arguments[$name])) ? '('.$arguments[$name].')' : '(\w+)';
                } else {
                    throw new \InvalidArgumentException('Format route path not valid!');
                }

            }, $path);
        }
    }