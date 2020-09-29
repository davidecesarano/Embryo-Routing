<?php 
    
    /**
     * RoutingMiddleware
     * 
     * Middleware to use Router for handler discovery.
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing 
     */

    namespace Embryo\Routing\Middleware;
    
    use RuntimeException;
    use Embryo\Routing\Interfaces\{RouteInterface, RouterInterface};
    use Embryo\Routing\Resolvers\{CallableResolver, ControllerResolver};
    use Embryo\Routing\Exceptions\{MethodNotAllowedException, NotFoundException};
    use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
    use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

    class RoutingMiddleware implements MiddlewareInterface 
    {   
        /**
         * @var RouterInterface $router
         */ 
        private $router;

        /**
         * Set router.
         *
         * @param RouterInterface $router
         */
        public function __construct(RouterInterface $router)
        {
            $this->router = $router;
        }

        /**
         * Process a server request and return a response.
         *
         * @param ServerRequestInterface $request 
         * @param RequestHandlerInterface $handler 
         * @return ResponseInterface 
         * @throws MethodNotAllowedException
         * @throws RuntimeException
         * @throws NotFoundException
         */
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $route = $this->router->dispatch($request);
            if ($route instanceof RouteInterface) {

                $status = $route->getStatus();
                switch($status) {
                    case 200:
                        $request = $request->withAttribute('route', $route);
                        return $handler->handle($request);
                    case 405:
                        throw new MethodNotAllowedException;
                    default:
                        throw new RuntimeException('Internal Server Error', 500);
                }
                
            } else {
                throw new NotFoundException;
            }
        }
    }