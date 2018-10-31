<?php 
    
    /**
     * RoutingMiddleware
     * 
     * Stores the route handler and arguments in request attributes.
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing 
     */

    namespace Embryo\Routing\Middleware;
    
    use Embryo\Http\Factory\ResponseFactory;
    use Embryo\Routing\Interfaces\{RouteInterface, RouterInterface};
    use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
    use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

    class RoutingMiddleware implements MiddlewareInterface 
    {
        /**
         * @var RouterInterface $router
         */    
        private $router;

        /**
         * Sets router.
         *
         * @param RouterInterface $container 
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
         */
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $route = $this->router->dispatcher($request);
            if ($route instanceof RouteInterface) {

                $request  = $request->withAttribute('route', $route);
                return $handler->handle($request);

            } else {
                return (new ResponseFactory)->createResponse(404);
            }      
        }
    }