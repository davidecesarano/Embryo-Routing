<?php 
    
    /**
     * RequestHandlerMiddleware
     * 
     * Executes request handlers discovered by a router.
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing 
     */

    namespace Embryo\Routing\Middleware;
    
    use Embryo\Http\Server\RequestHandler;
    use Embryo\Routing\Resolvers\{CallableResolver, ControllerResolver};
    use Psr\Container\ContainerInterface;
    use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
    use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

    class RequestHandlerMiddleware implements MiddlewareInterface 
    {   
        /**
         * @param ContainerInterface $container
         */ 
        private $container;

        /**
         * Set container.
         *
         * @param ContainerInterface $container
         */
        public function __construct(ContainerInterface $container)
        {
            $this->container = $container;
        }

        /**
         * Process a server request and return a response.
         *
         * @param ServerRequestInterface $request 
         * @param RequestHandlerInterface $handler 
         * @return ResponseInterface 
         * @throws InvalidArgumentException
         */
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $route      = $request->getAttribute('route');
            $callback   = $route->getCallback();
            $namespace  = $route->getNamespace();
            $middleware = $route->getMiddleware();
            $response   = $handler->handle($request);

            if (!is_callable($callback) && !is_string($callback)) {
                throw new \InvalidArgumentException('Callback must be a callable or a string.');
            }

            if (is_callable($callback)) {
                $resolver = new CallableResolver($callback);
            }

            if (is_string($callback)) {
                $resolver = new ControllerResolver($callback);
                $resolver->setNamespace($namespace);
            }
            
            $resolver->setContainer($this->container);

            $requestHandler = new RequestHandler($middleware);
            $requestHandler->add($resolver);
            return $requestHandler->dispatch($request, $response);
        }
    }