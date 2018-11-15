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
         * @return ResponseInterface
         */
        protected function execute(callable $callable, array $args): ResponseInterface
        {   
            ob_start();
            $level  = ob_get_level();

            try {

                $return = call_user_func_array($callable, $args);

                if ($return instanceof ResponseInterface) {
                    $response = $return;
                    $return = '';
                } elseif (is_null($return) || is_scalar($return) || (is_object($return) && method_exists($return, '__toString'))) {
                    
                    $response = (new ResponseFactory)->createResponse(200);
                    $response = $response->write($return);

                } elseif (is_array($return)) {

                    $response = (new ResponseFactory)->createResponse(200);
                    $response = $response->withHeader('Content-Type', 'application/json;charset=utf-8');
                    $response = $response->write(json_encode($return));
                    return $response;
                    
                } else {
                    throw new \UnexpectedValueException(
                        'The value returned must be scalar, array or an object with __toString method'
                    );
                }

                while (ob_get_level() >= $level) {
                    $return = ob_get_clean().$return;
                }

                $body = $response->getBody();
                if ($return !== '' && $body->isWritable()) {
                    $body->write($return);
                }
                return $response;

            } catch (\Exception $exception) {
                while (ob_get_level() >= $level) {
                    ob_end_clean();
                }
                throw $exception;
            }
        }
    }