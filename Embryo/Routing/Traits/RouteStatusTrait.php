<?php 

    /**
     * RouteStatusTrait
     * 
     * @author Davide Cesarano <davide.cesarano@unipegaso.it>
     * @link   https://github.com/davidecesarano/embryo-routing  
     */

    namespace Embryo\Routing\Traits;

    trait RouteStatusTrait
    {
        /**
         * Set status code.
         *
         * @param int $code
         * @return self
         */
        protected function setStatus(int $code): self
        {
            $this->status = $code;
            return $this;
        }
    }