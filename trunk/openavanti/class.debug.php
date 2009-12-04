<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.2.0-beta
 *
 */

    /**
     * Provides a method for debugging and logging information by specifying a callback function
     * to use as a debug handler.    
     *
     * @category    Debugging
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/debug
     */
    class Debug
    {
        private static $_callback = null;
        
        /**
         * Sets the callback function for debug logs.
         *
         * @argument callback The callback function to invoke when logging debug statements      
         * @returns void
         */
        public static function setDebugHandler($callback)
        {
            self::$_callback = $callback;
            
        } // setDebugHandler()
        
        
        /**
         * If the debug callback funciton is set to a valid, callable function, this method passes
         * the debug message to that callback function.              
         *
         * @argument string The debug message to send to the callback function       
         * @returns void
         */
        public static function log($message)
        {
            if(!is_null(self::$_callback))
            {
                if(is_callable(self::$_callback))
                {                   
                    call_user_func(self::$_callback, $message);
                }
            }
                
        } // log()
    
    } // Debug()

?>