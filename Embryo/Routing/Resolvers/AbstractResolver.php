<?php 

    /**
     * AbstractResolver
     * 
     * Resolves and executes a controller or callable route.
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing 
     */

    namespace Embryo\Routing\Resolvers;

    use Embryo\Container\Interfaces\ContainerBuilderInterface;
    use Embryo\Http\Message\{Response, ServerRequest};
    use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

    abstract class AbstractResolver 
    {
        /**
         * @var ContainerBuilderInterface $container
         */
        protected $container;

        /**
         * Set container.
         * 
         * @param ContainerBuilderInterface $container 
         * @return void
         */
        public function setContainer(ContainerBuilderInterface $container): void
        {
            $this->container = $container;
        }

        /**
         * Execute the callable.
         *
         * @param \Closure|array $callable
         * @param array $args
         * @param ResponseInterface $response
         * @return ResponseInterface
         * @throws \UnexpectedValueException
         * @throws \RuntimeException
         */
        protected function execute($callable, array $args, ResponseInterface $response): ResponseInterface
        { 
            if (!is_callable($callable)) {
                throw new \UnexpectedValueException('Callback must be a callable variable');
            }
            $return = call_user_func_array($callable, $args);

            if ($return instanceof ResponseInterface) {
                return $return;
            } elseif (is_scalar($return) || (is_object($return) && method_exists($return, '__toString'))) {
                
                $body = $response->getBody();
                $body->write(strval($return));
                return $response->withBody($body);

            } elseif (is_array($return)) {
                
                $json = json_encode($return, 32);
                if ($json === false) {
                    throw new \RuntimeException(json_last_error_msg(), json_last_error());
                }
                $body = $response->getBody();
                $body->write($json);
                $response = $response->withBody($body);
                return $response->withHeader('Content-Type', 'application/json;charset=utf-8');

            } else {
                throw new \UnexpectedValueException(
                    'The value returned must be scalar, array or an object with __toString method'
                );
            }
        }

        /**
         * Set processed request in Container.
         *
         * @param ServerRequestInterface $request
         * @return void
         */
        protected function setRequest(ServerRequestInterface $request)
        {
            $this->container->set('request', function() use($request){
                return $request;
            });
            $this->container->alias(ServerRequest::class, 'request');
        }

        /**
         * Set processed response in Container.
         *
         * @param ResponseInterface $response
         * @return void
         */
        protected function setResponse(ResponseInterface $response)
        {
            $this->container->set('response', function() use($response){
                return $response;
            });
            $this->container->alias(Response::class, 'response');
        }
    }