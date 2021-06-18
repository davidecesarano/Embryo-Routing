<?php 
    
    /**
     * CallableResolver
     * 
     * Resolves and executes a callable route.
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing 
     */

    namespace Embryo\Routing\Resolvers;
    
    use Embryo\Routing\Resolvers\AbstractResolver;
    use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
    use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
    
    class CallableResolver extends AbstractResolver implements MiddlewareInterface
    {
        /**
         * @var \Closure $callable
         */
        protected $callable;

        /**
         * Set callable.
         *
         * @param \Closure $callable
         */
        public function __construct(\Closure $callable)
        {
            $this->callable = $callable;
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
            $response = $handler->handle($request);

            $this->setRequest($request);
            $this->setResponse($response);
            
            $closure  = $this->callable;
            $callable = \Closure::bind($closure, $this->container->build());
            $params   = $this->getDefaultValueParameters(); 
            $args     = $this->setArguments($request, $response, $params);
            $response = $this->execute($callable, $args, $response);
            return $response;
        }

        /**
         * Return default value parameters.
         *
         * @return array
         */
        private function getDefaultValueParameters(): array
        {
            $ref = new \ReflectionFunction($this->callable);
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
         * @param ResponseInterface $response
         * @param array $params
         * @return array 
         */
        private function setArguments(ServerRequestInterface $request, ResponseInterface $response, array $params): array
        {
            $args[] = $request;
            $args[] = $response;
            $arguments = $request->getAttribute('route')->getArguments();
            if (!empty($arguments)) {
                foreach ($arguments as $name => $argument) {
                    $args[] = ($argument) ? $argument : $params[$name];
                }
            }
            return $args;
        }
    }