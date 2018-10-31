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

    abstract class Controller 
    {
        /**
         * @var ContainerInterface 
         */    
        protected $container;
        
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
    }