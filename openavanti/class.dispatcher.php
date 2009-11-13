<?php
/***************************************************************************************************
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
     * Dispatcher to route URI request to appropriate controller / method, and loads view files
     * based on instructions from the controller, passing data setup by the controller from the
     * controller to the view file.          
     *
     * @category    Database
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/dispatcher
     */
    class Dispatcher
    {
        // Stores a list of routes that determine how to map a URI:
        protected $aRoutes = array();
        
        // Toggles whether to require view files: 
        //protected $bRequireViewFiles = true;
        
        // Stores the name of the error controller that will handle 
        // dispatching errors:
        protected $sErrorController = "ErrorController";
        
        // Stores a reference to the Request object for this request:
        protected $oRequest = null;
        
        // Stores a reference to the Response object for this transaction:
        protected $oResponse = null;
        
        // Stores a reference to the Controller that will handle this requet:
        protected $oController = null;
        
        // Stores a reference to the View that will render the page:
        protected $oView = null;
                
        
        /**
         * Allows the specification of a callback method to invoke upon a 404 error, instead of 
         * requiring the 404 view file. If this callback is not callable, nothing will happen 
         * on a 404 error.           
         * 
         * @argument callback The callback to invoke upon a 404 error        
         * @returns void
         */
        public function set404Handler( $xCallback )
        {
            $this->x404Callback = $xCallback;
            
        } // set404Handler()


        /**
         * Sets up a custom route to match a URI against when dispatching the URI. A custom route
         * is simply run by running a preg_match against the URI, and rewriting it to the 
         * replacement on a valid match using preg_replace. 
         * 
         * @argument string The pattern to match against
         * @argument string The string to rewrite to
         * @returns void
         */
        public function addRoute( $sPattern, $sReplacement )
        {
            $this->aRoutes[] = array(
                "pattern" => $sPattern,
                "replace" => $sReplacement
            );
        
        } // addRoute()
    
    
        /**
         * Returns the Request object for the current request
         * 
         * @returns Request The current Request object
         */
        public function &getRequest()
        {
            return $this->oRequest;
            
        } // getRequest()
    
    
        /**
         * Returns the Request object for the current request
         * 
         * @returns Request The current Request object
         */
        public function &getResponse()
        {
            return $this->oResponse;
            
        } // getRequest()
        
    
        /**
         * Routes the specified request to an associated controller and action (class and method). 
         * Loads any specified view file stored in the controller and passes along any data stored
         * in the controller. 
         * 
         * This method checks for custom routes first (see AddRoute()), before checking for standard
         * routes. A standard route is a UI formed as follows /[controllername]/[methodname] where
         * [controllername] is the name of the controller, without the word "controller"
         * 
         * A standard controller is named like: ExampleController. To invoke the index method of 
         * this controller, one would navigate to /example/index.
         * 
         * The data loaded via the controller's SetData() method is exploded and available for the
         * view file.                                    
         * 
         * @argument string The current request URI
         * @returns void
         */
        public function connect( $sRequest )
        {
            $this->oRequest = new Request();
            $this->oRequest->sURI = $sRequest;
            
            $this->oResponse = new Response();
            
            $sController = "";
            $sAction = "";
            $aArguments = array();
            
            // Load an empty controller. This may be replaced if we found a controller through a route.
            // FIXME Why were we doing this?
            
            //$this->oController = new Controller( $this );
            
            //$this->oView = &$this->oController->GetView();
            
            // Loop each stored route and attempt to find a match to the URI:
            
            foreach( $this->aRoutes as $aRoute )
            {               
                if( preg_match( $aRoute[ "pattern" ], $sRequest ) != 0 )
                {
                    $sRequest = preg_replace( $aRoute[ "pattern" ], $aRoute[ "replace" ], $sRequest );
                }
            }
            
            if( substr( $sRequest, 0, 1 ) == "/" )
            {
                $sRequest = substr( $sRequest, 1 );
            }
            
            $this->oRequest->sRewrittenURI = $sRequest;
            
            
            // Explode the request on /
            $aRequest = explode( "/", $sRequest );
            
            // Store this as the last request:
            // FIXME - what was the point of this?
            //$_SESSION[ "last-request" ] = $aRequest;
            
            $this->oRequest->sControllerName = count( $aRequest ) > 0 ? 
                str_replace( "-", "_", array_shift( $aRequest ) ) . "Controller" : "";
            
            $this->oRequest->sAction = count( $aRequest ) > 0 ? 
                str_replace( "-", "_", array_shift( $aRequest ) ) : "index";
                
            $this->oRequest->aArguments = !empty( $aRequest ) ? $aRequest : array();
                
            
            // If we've found a controller and the class exists:
            if( !empty( $this->oRequest->sControllerName ) && 
                class_exists( $this->oRequest->sControllerName, true ) )
            {
                // Replace our empty controller with the routed one:                
                $this->oController = new $this->oRequest->sControllerName( $this );
                
                $this->oView = $this->oController->GetView();
                
                // Attempt to invoke an action on this controller:              
                $this->invokeAction();
            }
            else
            {
                // If we can't find the controller, we must throw an error:
                return( $this->handleError( ErrorHandler::CONTROLLER_NOT_FOUND ) );
            }
            
            // Continue on with the view loader method which will put the appropriate presentation
            // on the screen:
            
            try
            {
                $this->oView->renderPage();
            }
            catch( Exception $e )
            {
                $this->handleError( $e->getMessage() );
            }
            
            return( $this->oRequest );
        
        } // connect()
        
        
        /**
         * Called from connect(), responsible for calling the method of the controller
         * routed from the URI
         * 
         * @returns void
         */
        protected function invokeAction()
        {
            // is_callable() is used over method_exists() in order to properly utilize __call()
            
            if( !empty( $this->oRequest->sAction ) && 
                is_callable( array( $this->oController, $this->oRequest->sAction ) ) )
            {
                // Call $oController->$sAction() with arguments $aArguments:
                call_user_func_array( array( $this->oController, $this->oRequest->sAction ), 
                    $this->oRequest->aArguments );
            }
            else if( empty( $this->oRequest->sAction ) )
            {
                // Default to the index file:
                $this->oController->index();
            }
            else
            {
                // Action is not callable, throw an error:
                return( $this->handleError( ErrorHandler::ACTION_NOT_FOUND ) );
            }
        
        } // invokeAction()
        
        
        /**
         * Handles errors occurring during the Dispatch process and passes them off to the 
         * defined ErrorController, or throws an exception if the controller does not exist.
         * 
         * @returns void
         */
        public function handleError( $sErrorCode )
        {
            if( !empty( $this->sErrorController ) && class_exists( $this->sErrorController, true ) )
            {
                $oController = new $this->sErrorController( $this );
                $oController->error( $sErrorCode );
            }
            else
            {
                throw new Exception( "No ErrorController configured; cannot handle error" );
            }
            
        } // handleError()
        
        
        /**
         * Called to handle a 404 error
         * 
         * @deprecated Use handleError( ErrorHandler::FILE_NOT_FOUND );
         * @returns void
         */
        protected function invoke404Error()
        {
            if( !headers_sent() )
            {
                header( "HTTP/1.0 404 Not Found", true, 404 );
            }
            
            $this->handleError( ErrorHandler::FILE_NOT_FOUND );
        
        } // Invoke404Error()
        
    } // Dispatcher()

?>
