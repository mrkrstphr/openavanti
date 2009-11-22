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
        // Stores a reference to the dispatcher that spawned this controller:
        protected $oDispatcher = null;
        
        // Stores a reference to the view file that will render the page:
        protected $oView = null;
        
        
        /**
         * The final constructor; sets up data for the controller and calls 
         * init() to do user intialization.
         * 
         * @final
         * @arguments Dispatcher The dispatcher class that loaded this controller
         */
        public final function __construct( &$oDispatcher )
        {
            $this->oDispatcher = &$oDispatcher;
            
            $this->oView = new View( $this );
            
            $this->setDefaultView();
            
            $this->init();
            
        } // __construct()
        
        
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
         * This method uses the name of a controller and the name of an action
         * to try to guess the name of the view file that should be loaded. 
         * The constructed view file name is in the form of: 
         *  
         *      [controller]/[action].php
         * 
         * If no arguments are passed to this method, the current controller
         * and action are pulled from the Request object to construct the 
         * view file name.
         * 
         * @argument string Optional; The name of the controller
         * @argument string Optional; The name of the action
         * @returns void
         */
        protected function setDefaultView( $sController = null, $sAction = null )
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
            
            $this->oView->SetView( strtolower( $sController . "/" . $sAction . ".php" ) );
            
        } // setDefaultView()
        
        
        /**
         * Every controller must have an index() method defined for default requests to the 
         * controller that do not define a method. Since it is a requirement for this method to 
         * exist, it is defined in the parent controller class.                                                  
         * 
         * @returns void 
         */
        public function index()
        {
            $this->getResponse()->set404Error( true );
                
        } // index()
        
        
        
        /**
         * Returns a copy of the Dispatcher class that handled the current request and loaded
         * this controller. 
         * 
         * @returns Dispatcher The Dispatcher class that handled this request and loaded the 
         *      controller
         */
        public function &getDispatcher()
        {
            return $this->oDispatcher;
        
        } // getDispatcher()
        
        
        /**
         * Returns a copy of the Dispatcher's Request object which contains information about
         * the current request.
         *
         * @returns Request The Request object containing information about the current request
         */
        public function &getRequest()
        {
            return $this->oDispatcher->getRequest();
            
        } // getRequest()
        
        
        /**
         * Returns a copy of the Dispatcher's Response object which contains information about
         * the HTTP response.
         *
         * @returns Request The Response object containing information about the HTTP response
         */
        public function &getResponse()
        {
            return $this->oDispatcher->getResponse();
            
        } // getResponse()
        
        
        /**
         * Determines whether or not the current HTTP request came via AJAX.                                             
         * 
         * @deprecated Use getRequest()->isAjaxRequest()
         * @returns boolean True of the request is via AJAX, false otherwise 
         */
        public function isAjaxRequest()
        {
            return Dispatcher::IsAjaxRequest();
        
        } // isAjaxRequest()
        
        
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
        public function redirectTo( $sURL, $bPermanentRedirect = true )
        {
            if( !headers_sent() )
            {
                header( "Location: {$sURL}", true, $bPermanentRedirect ? 301 : null );
                
                return true;
            }
            
            return false;
            
        } // redirectTo()
        
        
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
        public function forwardAction($sAction, $sController = null, $xArguments = null)
        {
            $this->setDefaultView($sController, $sAction);
            
            $oController = &$this;
            
            if( !is_null( $sController ) )
            {
                $sController = $sController . "Controller";
                $oController = new $sController( $this->getDispatcher() );
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
            
            $this->oView->SetView( $oController->sView ); 
            $this->oView->aData = $oController->aData;
            
        } // forwardAction()
        
        
        /**
         * Sets the view file that should be loaded at the end of the request. This method does not
         * check to ensure that the file specified actually exists. It is up to the code that loads
         * the view file to do this (normally the Dispatcher class).                 
         *       
         * @deprecated Use $this->oView->setView()
         * @argument string The file name of the view file that should be loaded.
         * @returns void
         */ 
        public function setView( $sView )
        {
            $this->oView->setView( $sView );
        
        } // setView()
        
        
        /**
         * Returns a reference to the View object that will render the page
         * 
         * @returns View A refernece to the View that will render the page
         */ 
        public function &getView()
        {
            return $this->oView;
        
        } // getView()
        
        
        /**
         * Sets a data variable that can be used by the view file. Supplying the name and value
         * of the variable, before loading the view file, these variables will be extracted and
         * available in the view file for processing and/or display.
         * 
         * If the supplied variable already exists, it will be overwritten.                                  
         *
         * @deprecated Use $this->oView->[name] = [value]
         * @argument string The name of the variable to set
         * @argument mixed The value of the variable to set                  
         * @returns void
         */ 
        public function setData( $sName, $sValue )
        {
            $this->oView->$sName = $sValue;
            
        } // setData()
        
        
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
        public function setFlash( $sMessage, $sScope = "flash" )
        {
            $_SESSION[ (string)$sScope ] = $sMessage;
            
        } // setFlash()
        
        
        /**
         * Retrieves any flash message stored in the flash session variable, if any. See the
         * SetFlash() method.        
         *
         * @argument string The scope or name of the flash message to get; default = flash
         * @returns string The flash message, if any, stored in the session
         */ 
        public function getFlash( $sScope = "flash" )
        {
            $sFlash = isset( $_SESSION[ (string)$sScope] ) ? 
                $_SESSION[ (string)$sScope ] : "";
            
            unset( $_SESSION[ (string)$sScope ] );
            
            return $sFlash;
            
        } // getFlash()

    } // Controller()

?>