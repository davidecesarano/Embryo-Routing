<?php 

    /**
     * RouteActionTrait
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing  
     */

    namespace Embryo\Routing\Traits;

    trait RouteActionTrait 
    {
        /**
         * Sets one or more middleware for specific route.
         *
         * @param array $middleware
         * @return self 
         */
        public function middleware(...$middleware): self
        {
            $this->middleware = array_merge($this->middleware, $middleware);
            return $this;
        }

        /**
         * Sets specific regex for arguments.
         *
         * @param string|array $name 
         * @param string|null $regex
         * @return self 
         */
        public function where($name, $regex = null): self
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
         * @return self
         */
        public function name(string $name): self
        {
            $this->name = $name;
            return $this;
        }
    }