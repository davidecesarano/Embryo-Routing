<?php 
    
    /**
     * ControllerResolver
     * 
     * Resolves and executes a controller route.
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing 
     */

    namespace Embryo\Routing\Resolvers;
    
    use Embryo\Routing\Controller;
    use Embryo\Routing\Resolvers\AbstractResolver;
    use Psr\Container\ContainerInterface;
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
         * @var ContainerInterface $container
         */
        protected $container;

        /**
         * Set container.
         * 
         * @param string $controller 
         * @return self
         */
        public function __construct(string $controller)
        {
            if (strpos($controller, '@') === false) {
                throw new \InvalidArgumentException("$controller must be a 'class@method' string.");
            }
            $this->controller = $controller;
        }

        /**
         * Set namespace.
         *
         * @param string $namespace
         * @return self
         */
        public function setNamespace(string $namespace): self
        {
            $this->namespace = $namespace;
            return $this;
        }

        /**
         * Set container.
         * 
         * @param ContainerInterface $container 
         */
        public function setContainer(ContainerInterface $container)
        {
            $this->container = $container;
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
            $controller = $this->resolve($request, $response);
            $params     = $this->getDefaultValueParameters($controller);
            $args       = $this->setArguments($request, $params);
            $response   = $this->execute($controller, $args, $response);
            return $response;
        }

        /**
         * Returns controller's class.
         * 
         * @param ServerRequestInterface $request 
         * @param ResponseInterface $response 
         * @return array
         */
        private function resolve(ServerRequestInterface $request, ResponseInterface $response): array
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

            $class = $this->container->reflection($class);
            $class->setContainer($this->container);
            $class->setRequest($request);
            $class->setResponse($response);

            return [$class, $method];
        }

        /**
         * Return default value parameters.
         *
         * @return array
         */
        private function getDefaultValueParameters(array $controller): array
        {
            $ref = new \ReflectionMethod($controller[0], $controller[1]);
            $params = [];
            foreach ($ref->getParameters() as $value) {
                $params[$value->getName()] = ($value->isDefaultValueAvailable()) ? $value->getDefaultValue() : null;
            }
            return $params;
        }

        /**
         * Set and return arguments.
         * 
         * @param ServerRequestInterface $request
         * @param array $params
         * @return array 
         */
        private function setArguments(ServerRequestInterface $request, array $params): array
        {
            $args = [];
            $arguments = $request->getAttribute('route')->getArguments();
            if (!empty($arguments)) {
                foreach ($arguments as $name => $argument) {
                    $args[] = ($argument) ? $argument : $params[$name];
                }
            }
            return $args;
        }
    }