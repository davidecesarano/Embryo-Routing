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

    use Embryo\Http\Factory\ResponseFactory;
    use Psr\Container\ContainerInterface;
    use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

    abstract class AbstractResolver 
    {
        /**
         * @var ContainerInterface $container
         */
        protected $container;

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
         * Execute the callable.
         *
         * @param callable $callable
         * @param array $args
         * @param ResponseInterface $response
         * @return ResponseInterface
         */
        protected function execute(callable $callable, array $args, ResponseInterface $response): ResponseInterface
        { 
            $return = call_user_func_array($callable, $args);

            if ($return instanceof ResponseInterface) {
                return $return;
            } elseif (is_null($return) || is_scalar($return) || (is_object($return) && method_exists($return, '__toString'))) {
                return $response->write($return);
            } elseif (is_array($return)) {
                return $response->withJson($return);
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
        }
    }