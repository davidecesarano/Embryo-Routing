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
    
    use Embryo\Routing\Resolvers\AbstractResolver;
    use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
    use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
    
    class ControllerResolver extends AbstractResolver implements MiddlewareInterface
    {
        /**
         * @var string $namespace
         */
        private $namespace;

        /**
         * @var string $controller
         */
        private $controller;

        /**
         * Set controller.
         * 
         * @param string $controller 
         * @throws \InvalidArgumentException
         */
        public function __construct(string $controller)
        {
            if (strpos($controller, '@') === false) {
                throw new \InvalidArgumentException("$controller must be a 'class@method' string");
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
         * Process a server request and return a response.
         * 
         * @param ServerRequestInterface $request 
         * @param RequestHandlerInterface $handler 
         * @return ResponseInterface 
         */
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $this->setRequest($request);
            $response   = $handler->handle($request);
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
         * @throws \RuntimeException
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

            $class = $this->container->get($class);
            $class->setContainer($this->container->build());
            $class->setRequest($request);
            $class->setResponse($response);

            return [$class, $method];
        }

        /**
         * Return default value parameters.
         *
         * @param array $controller
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