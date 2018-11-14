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
        private $container;
        
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
         * Get service from Container.
         *
         * @param string $name
         * @return mixed
         */
        final public function get(string $name)
        {
            return $this->container->get($name);
        }
    }