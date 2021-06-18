<?php 

    /**
     * RouteActionTrait
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing  
     */

    namespace Embryo\Routing\Traits;

    use Embryo\Routing\Interfaces\RouteInterface;
    use Psr\Http\Server\MiddlewareInterface;

    trait RouteActionTrait 
    {
        /**
         * Sets one or more middleware for specific route.
         *
         * @param string|array|MiddlewareInterface $middleware
         * @return RouteInterface 
         * @throws \InvalidArgumentException
         */
        public function middleware($middleware): RouteInterface
        {
            if (!is_string($middleware) && !is_array($middleware) && !$middleware instanceof MiddlewareInterface) {
                throw new \InvalidArgumentException('Middleware must be a string, an array or an instance of MiddlewareInterface');
            }

            if (is_array($middleware)) {
                foreach ($middleware as $m) {
                    array_push($this->middleware, $m);
                }    
            }

            array_push($this->middleware, $middleware);
            return $this;
        }

        /**
         * Sets specific regex for arguments.
         *
         * @param string|array $name 
         * @param string|null $regex
         * @return RouteInterface 
         */
        public function where($name, $regex = null): RouteInterface
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
         * Sets name of specific route.
         *
         * @param string $name
         * @return RouteInterface
         */
        public function name(string $name): RouteInterface
        {
            $this->name = $name;
            return $this;
        }
    }