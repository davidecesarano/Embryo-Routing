<?php 

    /**
     * NotFoundException
     */
    
    namespace Embryo\Routing\Exceptions;

    class NotFoundException extends \Exception 
    {
        /**
         * Set message.
         * 
         * @param string $message
         */
        public function __construct($message = 'Not Found')
        {
            parent::__construct($message, 404);
        }
    }