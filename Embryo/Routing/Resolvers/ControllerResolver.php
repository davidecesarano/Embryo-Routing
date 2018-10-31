<?php 
    
    /**
     * ControllerResolver
     * 
     * Resolves and executes a controller route.
     */

    namespace Embryo\Routing\Resolvers;
    
    use Embryo\Routing\Controller;
    use Embryo\Routing\Resolvers\AbstractResolver;
    use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
    
    class ControllerResolver extends AbstractResolver
    {    
        /**
         * @var string $controller
         */
        private $controller;

        /**
         * @var string $namespace
         */
        private $namespace;

        /**
         * Sets container.
         * 
         * @param string $controller 
         * @return self
         */
        public function __construct(string $controller)
        {
            if (strpos($controller, '@') === false || !is_string($controller)) {
                throw new \InvalidArgumentException("$controller must be a string");
            }
            $this->controller = $controller;
        }

        /**
         * Sets namespace.
         *
         * @param string $namespace
         * @return self
         */
        public function setNamespace($namespace)
        {
            $this->namespace = $namespace;
            return $this;
        }

        /**
         * Process a server request and return a response.
         * 
         * @param ServerRequestInterface $request 
         * @param ResponseInterface $response 
         * @return ResponseInterface 
         */
        public function process(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
        {
            $controller = $this->resolve();
            $params     = $this->getDefaultValueParameters($controller);
            $args       = $this->setArguments($request, $response, $params);
            $response   = $this->execute($controller, $args);
            return $response;
        }

        /**
         * Returns controller's class.
         * 
         * @param string $controller
         * @return array
         */
        private function resolve()
        {
            $name   = explode('@', $this->controller)[0];
            $method = explode('@', $this->controller)[1];
            $class  = $this->namespace.'\\'.$name;

            if (!class_exists($class)) {
                throw new \RuntimeException("$class class does not exist");
            }

            if (!method_exists($class, $method)) {
                throw new \RuntimeException("$method method of the ".get_class($class)." class does not exist");
            }

            $class = $this->container->get($class);
            $class->setContainer($this->container);
            return [$class, $method];
        }

        public function getDefaultValueParameters(array $controller)
        {
            $ref = new \ReflectionMethod($controller[0], $controller[1]);
            $params = [];
            foreach ($ref->getParameters() as $value) {
                $params[$value->getName()] = ($value->isDefaultValueAvailable()) ? $value->getDefaultValue() : null;
            }
            return $params;
        }
    }