<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    Dispatcher
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.2.0-beta
 */
 
 
    /**
     * A default controller class to be extended
     *
     * @category    Controller
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/controller
     */
    class Controller
    {
        protected $oDispatcher = null;
        
        public $aData = array();
        public $sLayout = "";
        public $sView = "";
        
        public $b404Error = false;
        
        
        /**
         * The final constructor; sets up data for the controller and calls init()
         * 
         * @final
         * @arguments Dispatcher The dispatcher class that loaded this controller
         */
        public final function __construct( &$oDispatcher )
        {
            $this->oDispatcher = &$oDispatcher;
                        
            $this->SetDefaultView();

            $this->init();
            
        } // __construct()
        
        
        /**
         * 
         * 
         */
        protected function SetDefaultView( $sController = null, $sAction = null )
        {
            if( empty( $sController ) )
            {
                $sController = substr( get_class( $this ), 0, 
                    strlen( get_class( $this ) ) - strlen( "Controller" ) );
            }
            
            if( empty( $sAction ) )
            {
                $sAction = $this->GetRequest()->sAction;
            }
            
            $this->sView = strtolower( $sController . "/" . $sAction . ".php" );
            
        } // SetDefaultView()
        
        
        /**
         * Provides initialization mechanism for the Controller class and is called by the
         * constructor. Subclasses cannot override the constructor due to the possibility of not
         * passing the correct required parameters.
         * 
         * @returns void
         */
        public function init()
        {
            
        } // init()
        
        
        /**
         * Every controller must have an index() method defined for default requests to the 
         * controller that do not define a method. Since it is a requirement for this method to 
         * exist, it is defined in the parent controller class.                                                  
         * 
         * @returns void 
         */
        public function index()
        {
            $this->b404Error = true;
                
        } // index()
        
        
        
        /**
         * Returns a copy of the Dispatcher class that handled the current request and loaded
         * this controller. 
         * 
         * @returns Dispatcher The Dispatcher class that handled this request and loaded the 
         *      controller
         */
        public function GetDispatcher()
        {
            return( $this->oDispatcher );
        
        } // GetDispatcher()
        
        
        /**
         * Returns a copy of the Dispatcher's Request object which contains information about
         * the current request.
         *
         * @returns Request The Request object containing information about the current request
         */
        public function GetRequest()
        {
            return( $this->oDispatcher->GetRequest() );
            
        } // GetRequest()
        
        
        /**
         * Returns the internal 404 status to determine if a 404 error flag was triggered
         *
         * @returns bool True if a 404 error was encountered, false otherwise
         */                             
        public function Is404Error()
        {
            return( $this->b404Error );
            
        } // Is404Error()
        
        
        /**
         * Sets or clears the internal 404 error status flag. 
         * 
         * @argument bool True to trigger a 404 error, false to clear the 404 flag, default: true
         * @returns void
         */ 
        public function Set404Error( $bIs404Error = true )
        {
            $this->b404Error = $bIs404Error;
            
        } // Set404Error()
        
        
        /**
         * Determines whether or not the current HTTP request came via AJAX.                                             
         * 
         * @returns boolean True of the request is via AJAX, false otherwise 
         */
        public function IsAjaxRequest()
        {
            return( Dispatcher::IsAjaxRequest() );
        
        } // IsAjaxRequest()
        
        
        /**
         * Sets the HTTP status code header. This method will only work if no output or headers
         * have already been sent.
         *       
         * @argument int The HTTP status code
         * @returns bool True if the operation was successful, false on failure
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
         * @argument string The error message to output
         * @argument int The response code to send to the browser, default: 400
         * @returns void                         
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
         * @argument string The URL to redirect to 
         * @argument bool True to signal a permanent redirect, false to not set the HTTP response code       
         * @returns bool True if the redirect was sucessfull, false otherwise        
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
         * Similar to RedirectTo(), forwardAction() sends processing to the specified action, 
         * or controller and action. An optional arguments parameter allows you to pass information
         * to this action, and the view and data setup by the new action will be copied to this
         * controller.
         * 
         * @argument string The action to which processing should be forwarded
         * @argument string The controller to which processing should be forwarded to, used in
         *      conjunction with the specified action.
         * @argument mixed The data to be forwarded as arguments to the action. Can be a scalar 
         *      value or an array of values. 
         * @returns void
         */
        public function forwardAction( $sAction, $sController = null, $xArguments = null )
        {
            $this->SetDefaultView( $sController, $sAction );
            
            $oController = &$this;
            
            if( !is_null( $sController ) )
            {
                $sController = $sController . "Controller";
                $oController = new $sController( $this->GetDispatcher() );
            }
            
            if( !is_callable( array( $oController, $sAction ) ) )
            {
                return;
            }
            
            if( is_array( $xArguments ) )
            {
                call_user_func_array( array( $oController, $sAction ), $xArguments );
            }
            else if( is_scalar( $xArguments ) )
            {
                $oController->$sAction( $xArguments );
            }
            
            $this->sView = $oController->sView; 
            $this->aData = $oController->aData;
            
        } // forwardAction()
        
        
        /**
         * Sets the layout file to use for this controller. 
         * 
         * @argument string The file name of the layout file that should be loaded
         * @returns void
         */
        public function SetLayout( $sLayoutFile )
        {
            $this->sLayout = $sLayoutFile;
            
        } // SetLayout()
        
        
        /**
         * Sets the view file that should be loaded at the end of the request. This method does not
         * check to ensure that the file specified actually exists. It is up to the code that loads
         * the view file to do this (normally the Dispatcher class).                 
         *       
         * @argument string The file name of the view file that should be loaded.
         * @returns void
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
         * @argument string The name of the variable to set
         * @argument mixed The value of the variable to set                  
         * @returns void
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
         * @argument string The scope or name of the flash message to set; default = flash
         * @argument string The message to set in the flash session variable
         * @returns void
         */ 
        public function SetFlash( $sMessage, $sScope = "flash" )
        {
            $_SESSION[ (string)$sScope ] = $sMessage;
            
        } // SetFlash()
        
        
        /**
         * Retrieves any flash message stored in the flash session variable, if any. See the
         * SetFlash() method.        
         *
         * @argument string The scope or name of the flash message to get; default = flash
         * @returns string The flash message, if any, stored in the session
         */ 
        public function GetFlash( $sScope = "flash" )
        {
            $sFlash = isset( $_SESSION[ (string)$sScope] ) ? 
                $_SESSION[ (string)$sScope ] : "";
            
            unset( $_SESSION[ (string)$sScope ] );
            
            return( $sFlash );
            
        } // GetFlash()

    } // Controller()

?>
