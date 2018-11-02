<?php 

    /**
     * RouteMatchTrait
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing  
     */

    namespace Embryo\Routing\Traits;

    trait RouteMatchTrait
    {
        /**
         * Compose route path.
         * 
         * This method combines the basePath, prefix and route pattern
         * for the composition of the route path.
         * 
         * @return string
         */
        protected function composeRoutePath()
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
         * Return the route regex path.
         * 
         * If route doesn not specify the arguments with where() method, 
         * the arguments array is empty and function return the
         * 'digit' regex (\w+).
         * 
         * @param string $path
         * @return string
         * @throws InvalidArgumentException
         */
        protected function getRouteRegexPath(string $path): string
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

        /**
         * Set uri.
         *
         * @param string $uri
         * @return self
         */
        protected function setUri(string $uri): self
        {
            $this->uri = $uri;
            return $this;
        }

        /**
         * Set method.
         * 
         * @param string $method
         * @return self
         */
        protected function setMethod(string $method): self
        {
            $this->method = $method;
            return $this;
        }

        /**
         * Set route path.
         *
         * @param string $routePath
         * @return self
         */
        protected function setRoutePath(string $routePath): self
        {
            $this->routePath = $routePath;
            return $this;
        }

        /**
         * Set arguments.
         *
         * @param array $params
         * @param string $path
         * @return self
         */
        protected function setArguments(array $params, string $path): self
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

            }
            return $this;
        }
    }