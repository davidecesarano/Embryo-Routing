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

    use Embryo\Http\Server\MiddlewareDispatcher;
    use Embryo\Routing\Resolvers\{CallableResolver, ControllerResolver};
    use Psr\Container\ContainerInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;

    class RequestHandlerMiddleware implements MiddlewareInterface
    {
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
         */
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $route          = $request->getAttribute('route');
            $requestHandler = $route->getCallback();
            $middleware     = $route->getMiddleware();
            $namespace      = $route->getNamespace();
            $response       = $handler->handle($request);
            $dispatcher     = new MiddlewareDispatcher;

            if (is_string($middleware) || $middleware instanceof MiddlewareInterface) {
                $dispatcher->add($middleware);
                $response = $dispatcher->dispatch($request, $response);
            }

            if (is_array($middleware)) {
                foreach ($middleware as $m) {
                    $dispatcher->add($m);
                }
                $response = $dispatcher->dispatch($request, $response);
            }
            
            if (is_callable($requestHandler)) {
                $resolver = new CallableResolver($requestHandler);
            }

            if (is_string($requestHandler)) {
                $resolver = new ControllerResolver($requestHandler);
                $resolver->setNamespace($namespace);
            }

            $this->container->set('request', function() use($request){
                return $request;
            });

            $this->container->set('response', function() use($response){
                return $response;
            });
            
            $resolver->setContainer($this->container);
            $response = $resolver->process($request, $response);
            return $response;
        }
    }
