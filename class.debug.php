<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @package         openavanti
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 */


    /**
     * Provides a method for debugging and logging information by specifying a callback function
     * to use as a debug handler.    
     *
     * @category    Debugging
     * @author      Kristopher Wilson
     * @package     openavanti
     * @link        http://www.openavanti.com/documentation/docs/1.0.3/Debug
     */
    class Debug
    {
        /**
         * The callback method which is called for each log message
         */
        private static $cCallback = null;
        
        
        /**
         * Sets the callback function for debug logs.
         *
         * @param callback $cCallback The callback function to invoke when logging debug statements      
         */
        public static function SetDebugHandler( $cCallback )
        {
            self::$cCallback = $cCallback;
            
        } // SetDebugHandler()
        
        
        /**
         * If the debug callback funciton is set to a valid, callable function, this method passes
         * the debug message to that callback function.              
         *
         * @param string $sMessage The debug message to send to the callback function       
         */
        public static function Log( $sMessage )
        {
            if( !is_null( self::$cCallback ) )
            {
                if( is_callable( self::$cCallback ) )
                {                   
                    call_user_func( self::$cCallback, $sMessage );
                }
            }
                
        } // Log()
    
    } // Debug()

?>
