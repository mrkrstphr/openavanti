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
     * A simple timer for various purposes
     *
     * @category    Controller
     * @author      Kristopher Wilson
     * @package     openavanti
     * @link        http://www.openavanti.com/documentation/docs/1.0.3/StaticTimer
     */
    class StaticTimer
    {
        /**
         * The starting time
         */
        private static $iStart = 0;
        
        /**
         * The ending time
         */
        private static $iEnd = 0;
        
        
        /**
         * Starts the timer -- if a timer was already previously started, this action
         * will overwrite the start time
         */
        public static function Start()
        {
            self::Update( self::$iStart );
            
        } // Start()
        
        
        /**
         * Return the amount of time elapsed since starting the timer. If the timer was never
         * started, this will return 0. This does not actually stop the timer. For timing a series 
         * of events, Stop() can be called multiple time to get increments in between various steps                                                                          
         * 
         * @return double The amount of time that has passed since starting 
         */
        public static function Stop()
        {
            self::Update( self::$iEnd );
            
            return( self::$iStart == 0 ? self::$iStart : ( self::$iEnd - self::$iStart ) );
            
        } // Stop()
        
        
        /**
         * Internally used to update the supplied iVar with the current micro time.
         */
        protected static function Update( &$iVar )
        {           
            $iVar = microtime( true );
            
        } // Update()
    
    } // StaticTimer()

?>
