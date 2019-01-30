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
    
    class CallableResolver extends AbstractResolver
    {
        /**
         * @var callable $callable
         */
        protected $callable;

        /**
         * Set callable.
         *
         * @param callable $callable
         */
        public function __construct(callable $callable)
        {
            $this->callable = $callable;
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
            $callable = \Closure::bind($this->callable, $this->container);
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