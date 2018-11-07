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
         * @var string $attribute
         */
        private $attribute = 'route';

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
         * Set attribute name.
         *
         * @param string $name
         * @return self
         */
        public function setAttribute(string $name): self
        {
            $this->attribute = $name;
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
            $route = $this->router->dispatcher($request);
            if ($route instanceof RouteInterface) {

                $status = $route->getStatus();
                switch($status) {
                    case 200:
                        $request  = $request->withAttribute($this->attribute, $route);
                        return $handler->handle($request);
                    case 405:
                        return (new ResponseFactory)->createResponse(405);
                    default:
                        return (new ResponseFactory)->createResponse(500);
                }
                
            } else {
                return (new ResponseFactory)->createResponse(404);
            }      
        }
    }