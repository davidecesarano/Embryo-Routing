<?php
    
    /**
     * Controller
     * 
     * Class to exend from Controller. 
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing 
     */

    namespace Embryo\Routing;
    
    use Psr\Container\ContainerInterface;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;

    abstract class Controller 
    {
        /**
         * @var ContainerInterface 
         */    
        private $container;

        /**
         * @var ServerRequestInterface 
         */    
        protected $request;

        /**
         * @var ResponseInterface 
         */    
        protected $response;
        
        /**
         * Set Container.
         *
         * @param ContainerInterface $container
         * @return void
         */
        final public function setContainer(ContainerInterface $container)
        {
            $this->container = $container;
        }

        /**
         * Set Request.
         *
         * @param ContainerInterface $container
         * @return void
         */
        final public function setRequest(ServerRequestInterface $request)
        {
            $this->request = $request;
        }

        /**
         * Set Response.
         *
         * @param ContainerInterface $container
         * @return void
         */
        final public function setResponse(ResponseInterface $response)
        {
            $this->response = $response;
        }

        /**
         * Get service from Container.
         *
         * @param string $key
         * @return mixed
         */
        final public function get(string $key)
        {
            return $this->container->get($key);
        }
    }