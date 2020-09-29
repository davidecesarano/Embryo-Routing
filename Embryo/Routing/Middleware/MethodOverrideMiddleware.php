<?php 
    
    /**
     * MethodOverrideMiddleware
     * 
     * Override HTTP Request method by given body/query param or custom header.
     */

    namespace Embryo\Routing\Middleware;
    
    use Embryo\Http\Factory\ResponseFactory;
    use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
    use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

    class MethodOverrideMiddleware implements MiddlewareInterface 
    {
        /**
         * @var string $header
         */
        private $header = 'X-Http-Method-Override';
        
        /**
         * @var string $parsedBodyParameter
         */
        private $parsedBodyParameter = '_METHOD';
        
        /**
         * @var string $queryParameter
         */
        private $queryParameter = '_METHOD';

        /**
         * @var array $allowedMethods
         */
        private $allowedMethods = ['PATCH', 'PUT', 'DELETE', 'OPTIONS'];

        /**
         * Process a server request and return a response.
         *
         * @param ServerRequestInterface $request 
         * @param RequestHandlerInterface $handler 
         * @return ResponseInterface 
         */
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $method = $this->getOverrideMethod($request);
            if (!empty($method) && $method !== $request->getMethod()) {
                
                if (in_array($method, $this->allowedMethods)) {
                    $request = $request->withMethod($method);
                } else {
                    return (new ResponseFactory)->createResponse(405);
                }

            }
            return $handler->handle($request);  
        }

        /**
         * Returns override method.
         *
         * @param ServerRequestInterface $request
         * @return string
         */
        private function getOverrideMethod(ServerRequestInterface $request): string
        {
            if ($request->getMethod() === 'POST') {
                
                $params = $request->getParsedBody();
                $param = $this->parsedBodyParameter;
                if (is_array($params) && isset($params[$param])) {
                    return strtoupper($params[$param]);
                }
            
            } elseif ($request->getMethod() === 'GET') {

                $params = $request->getQueryParams();
                if (isset($params[$this->queryParameter])) {
                    return strtoupper($params[$this->queryParameter]);
                }

            }
            return strtoupper($request->getHeaderLine($this->header));
        }
    }