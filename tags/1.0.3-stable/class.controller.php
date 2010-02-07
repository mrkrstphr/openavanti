<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    Dispatcher
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @package         openavanti
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 */
 
 
    /**
     * A default controller class to be extended
     *
     * @category    Controller
     * @author      Kristopher Wilson
     * @package     openavanti
     * @link        http://www.openavanti.com/documentation/docs/1.0.3/Controller
     */
    class Controller
    {
        /**
         * Stores view data that is passed from the Controller to the View
         */
        public $aData = array();
        
        /**
         * Stores the name of the file file that will be loaded at the end of the request
         */
        public $sView = "";
        
        /**
         * Stores whether the current request will result in a 404 error
         */
        public $b404Error = false;
        
        
        /**
         * Constructor. Currently does not do anything.                                                      
         */
        public function __construct()
        {
        
        } // __construct()
                
        
        /**
         * Every controller must have an index() method defined for default requests to the 
         * controller that do not define a method. Since it is a requirement for this method to 
         * exist, it is defined in the parent controller class.
         */
        public function index()
        {
            $this->b404Error = true;
                
        } // index()
        
        
        /**
         * Returns the internal 404 status to determine if a 404 error flag was triggered
         *
         * @return bool True if a 404 error was encountered, false otherwise
         */                             
        public function Is404Error()
        {
            return( $this->b404Error );
            
        } // Is404Error()
        
        
        /**
         * Sets or clears the internal 404 error status flag. 
         * 
         * @param bool $bIs404Error True to trigger a 404 error, false to clear the 404 flag.
         *      Default: true
         */ 
        public function Set404Error( $bIs404Error = true )
        {
            $this->b404Error = $bIs404Error;
            
        } // Set404Error()
        
        
        /**
         * Determines whether or not the current HTTP request came via AJAX.                                             
         * 
         * @return boolean True of the request is via AJAX, false otherwise 
         */
        public function IsAjaxRequest()
        {
            return( Dispatcher::IsAjaxRequest() );
        
        } // IsAjaxRequest()
        
        
        /**
         * Sets the HTTP status code header. This method will only work if no output or headers
         * have already been sent.
         *       
         * @param int $iCode The HTTP status code
         * @return bool True if the operation was successful, false on failure
         */
        public function SetHTTPStatus( $iCode )
        {
            if( !headers_sent() )
            {
                header( " ", true, $iCode );
                
                return( true );
            }
            
            return( false );
            
        } // SetHTTPStatus()
        
        
        /**
         * This specialized method does two things: it attempts to set the HTTP status code,
         * 400 by default, to inform the web browser that there was an error, and second, 
         * echoes the supplied error message to the browser, which could be a simple string or
         * a JSON object.                
         *
         * @param string $sError The error message to output
         * @param int $iResponseCode Optional; The response code to send to the browser.
         *      Default: 400
         */                     
        public function AjaxError( $sError, $iResponseCode = 400 )
        {
            $this->SetHTTPStatus( $iResponseCode );
            
            echo $sError;
            
        } // AjaxError()
        
        
        /**
         * This method redirects the browser to the specified URL. The second argument controls
         * whether or not the 301 HTTP response code is used to signal a permanent redirect. Using
         * this response code enable the user to hit refresh afterwards without resubmitting any
         * form data from the original request.
         * 
         * If headers have already been sent to the browser, this method will return false and will
         * not call the redirect. Otherwise this method will always return true.                                                     
         *
         * @param string $sURL The URL to redirect to 
         * @param bool $bPermanentRedirect Optional; True to signal a permanent redirect, false to
         *      not set the HTTP response code. Default: true
         * @return bool True if the redirect was sucessfull, false otherwise        
         */ 
        public function RedirectTo( $sURL, $bPermanentRedirect = true )
        {
            if( !headers_sent() )
            {
                header( "Location: {$sURL}", true, $bPermanentRedirect ? 301 : null );
                
                return( true );
            }
            
            return( false );
            
        } // RedirectTo()
        
        
        /**
         * Sets the view file that should be loaded at the end of the request. This method does not
         * check to ensure that the file specified actually exists. It is up to the code that loads
         * the view file to do this (normally the Dispatcher class).                 
         *       
         * @param string $sView The file name of the view file that should be loaded.
         */ 
        public function SetView( $sView )
        {
            $this->sView = $sView;
        
        } // SetView()
        
        
        /**
         * Sets a data variable that can be used by the view file. Supplying the name and value
         * of the variable, before loading the view file, these variables will be extracted and
         * available in the view file for processing and/or display.
         * 
         * If the supplied variable already exists, it will be overwritten.                                  
         *
         * @param string $sName The name of the variable to set
         * @param mixed $sValue The value of the variable to set
         */ 
        public function SetData( $sName, $sValue )
        {
            $this->aData[ $sName ] = $sValue;
            
        } // SetData()
        
        
        /**
         * Sets a session variable called flash with the supplied message. This can be used on a
         * redirect to display a success message (in conjunction with the RedirectTo() method).      
         *
         * If a flash message is already set, it will be overwritten on subsequent calls.
         *               
         * @param string $sMessage The message to set in the flash session variable
         */ 
        public function SetFlash( $sMessage )
        {
            $_SESSION[ "flash" ] = $sMessage;
            
        } // SetFlash()
        
        
        /**
         * Retrieves any flash message stored in the flash session variable, if any. See the
         * SetFlash() method.        
         *
         * @return string The flash message, if any, stored in the session
         */ 
        public function GetFlash()
        {
            $sFlash = isset( $_SESSION[ "flash" ] ) ? $_SESSION[ "flash" ] : "";
            
            unset( $_SESSION[ "flash" ] );
            
            return( $sFlash );
            
        } // GetFlash()
    
    } // Controller()

?>
