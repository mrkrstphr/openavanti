<?php
/*******************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.2.0-beta
 *
 */

    /**
     * 
     *
     * @category    Response
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/response
     */
    class Response
    {
        // Stores whether or not the request resulted in a 404 error:
        protected $b404Error = false;
        
        protected $aHeaders = array();
        
        
        /**
         * 
         * 
         */
        public function __construct()
        {
            
        } // __construct()
        
        
        /**
         * 
         * 
         */
        public function setHeader( $sHeader, $sValue )
        {
            
        } // setHeader()
        
        
        /**
         * 
         * 
         */
        public function setHeaders( array $aHeaders )
        {
            
        } // setHeaders()
        
        
        /**
         * 
         * 
         */
        public function getHeader( $sHeader )
        {
            
        } // getHeader()
        
        
        /**
         * 
         * 
         */
        public function getHeaders()
        {
            
        } // getHeaders()
        
        
        /**
         * 
         * 
         */
        public function clearHeader( $sHeader )
        {
            
        } // clearHeader()
        
        
        /**
         * 
         * 
         */
        public function clearHeaders()
        {
            
        } // clearHeaders()
        
        
        /**
         * Sets the HTTP status code header. This method will only work if no output or headers
         * have already been sent.
         *       
         * @argument int The HTTP status code
         * @returns bool True if the operation was successful, false on failure
         */
        public function setHTTPStatus( $iCode )
        {
            if( !headers_sent() )
            {
                header( " ", true, $iCode );
                
                return( true );
            }
            
            return( false );
            
        } // setHTTPStatus()
        
        
        /**
         * This specialized method does two things: it attempts to set the HTTP status code,
         * 400 by default, to inform the web browser that there was an error, and second, 
         * echoes the supplied error message to the browser, which could be a simple string or
         * a JSON object.                
         *
         * @argument string The error message to output
         * @argument int The response code to send to the browser, default: 400
         * @returns void                         
         */                     
        public function ajaxError( $sError, $iResponseCode = 400 )
        {
            $this->SetHTTPStatus( $iResponseCode );
            
            echo $sError;
            
        } // ajaxError()
        
        
        /**
         * Returns the internal 404 status to determine if a 404 error flag was triggered
         *
         * @returns bool True if a 404 error was encountered, false otherwise
         */                             
        public function is404Error()
        {
            return $this->b404Error;
            
        } // is404Error()
        
        
        /**
         * Sets or clears the internal 404 error status flag. 
         * 
         * @argument bool True to trigger a 404 error, false to clear the 404 flag, default: true
         * @returns void
         */ 
        public function set404Error( $bIs404Error = true )
        {
            $this->b404Error = $bIs404Error;
            
        } // set404Error()


    } // Response()

?>
