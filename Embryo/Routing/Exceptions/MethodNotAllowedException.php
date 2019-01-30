<?php 

    /**
     * MethodNotAllowedException
     */
    
    namespace Embryo\Routing\Exceptions;

    class MethodNotAllowedException extends \Exception
    {
        /**
         * Set message.
         * 
         * @param string $message
         */
        public function __construct($message = 'Method Not Allowed')
        {
            parent::__construct($message, 404);
        }
    }