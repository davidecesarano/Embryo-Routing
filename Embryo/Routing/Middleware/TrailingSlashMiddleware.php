<?php 
    
    /**
     * TrailingSlashMiddleware
     * 
     * 
     */

    namespace Embryo\Routing\Middleware;
    
    use Embryo\Http\Factory\ResponseFactory;
    use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
    use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

    class TrailingSlashMiddleware implements MiddlewareInterface 
    {
        /**
         * Process a server request and return a response.
         *
         * @param ServerRequestInterface $request 
         * @param RequestHandlerInterface $handler 
         * @return ResponseInterface 
         */
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $uri  = $request->getUri();
            $path = $this->normalize($uri->getPath());

            if ($uri->getPath() !== $path) {
                return (new ResponseFactory)
                    ->createResponse(301)
                    ->withHeader('Location', (string) $uri->withPath($path));
            }
            return $handler->handle($request->withUri($uri->withPath($path)));  
        }

        /**
         * Normalize path.
         *
         * @param ServerRequestInterface $request
         * @return string
         */
        private function normalize(string $path): string
        {
            if ($path === '') {
                return '/';
            }

            if (strlen($path) > 1) {
                if (substr($path, -1) === '/' && !pathinfo($path, PATHINFO_EXTENSION)) {
                    return rtrim($path, '/');
                }
            }
            return $path;
        }
    }