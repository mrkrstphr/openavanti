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
        private $aRoutes = array();
        protected $bRequireViewFiles = true;
        
        protected $sErrorController = "ErrorController";
                
        protected $sDefaultLayout = "";
        
        protected $oRequest = null;
        
        
        /**
         * Toggles whether or not the controller should load view files (header, the view setup
         * by the controller, and the footer view file.          
         * 
         * @argument bool True if views should be required, false otherwise      
         * @returns void
         */
        public function RequireViewFiles( $bRequireViewFiles )
        {
            $this->bRequireViewFiles = $bRequireViewFiles;
            
        } // RequireViewFiles()

        
        /**
         * Sets the default layout file to load when one isn't specified for the specific
         * request via the controller. 
         * 
         * @argument string The name of the layout file to load when one isn't specified
         * @returns void
         */
        public function SetDefaultLayout( $sLayoutFile )
        {
            $this->sDefaultLayout = $sLayoutFile;
            
        } // SetDefaultLayout()
        
        
        /**
         * Allows the specification of a callback method to invoke upon a 404 error, instead of 
         * requiring the 404 view file. If this callback is not callable, nothing will happen 
         * on a 404 error.           
         * 
         * @argument callback The callback to invoke upon a 404 error        
         * @returns void
         */
        public function Set404Handler( $xCallback )
        {
            $this->x404Callback = $xCallback;
            
        } // Set404Handler()


        /**
         * Sets up a custom route to match a URI against when dispatching the URI. A custom route
         * is simply run by running a preg_match against the URI, and rewriting it to the 
         * replacement on a valid match using preg_replace. 
         * 
         * @argument string The pattern to match against
         * @argument string The string to rewrite to
         * @returns void
         */
        public function AddRoute( $sPattern, $sReplacement )
        {
            $this->aRoutes[] = array(
                "pattern" => $sPattern,
                "replace" => $sReplacement
            );
        
        } // AddRoute()
    
    
        /**
         * Returns the Request object for the current request
         * 
         * @returns Request The current Request object
         */
        public function GetRequest()
        {
            return( $this->oRequest );
            
        } // GetRequest()
        
    
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
        public function Connect( $sRequest )
        {
            $this->oRequest = new Request();
            $this->oRequest->sURI = $sRequest;
            
            $sController = "";
            $sAction = "";
            $aArguments = array();
            
            // Load an empty controller. This may be replaced if we found a controller through a route.
            
            $this->oRequest->oController = new Controller( $this );
            
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
            $_SESSION[ "last-request" ] = $aRequest;
            
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
                $this->oRequest->oController = new $this->oRequest->sControllerName( $this );
                
                // Attempt to invoke an action on this controller:              
                $this->InvokeAction();
            }
            else
            {
                // If we can't find the controller, we must throw an error:
                return( $this->HandleError( ErrorHandler::CONTROLLER_NOT_FOUND ) );
            }
            
            // Continue on with the view loader method which will put the appropriate presentation
            // on the screen:
            
            $this->LoadView();
        
            return( $this->oRequest );
        
        } // Connect()
        
        
        /**
         * Determines whether or not the current HTTP request came via AJAX.                                             
         * 
         * @returns boolean True of the request is via AJAX, false otherwise 
         */
        public static function IsAjaxRequest()
        {
            return( isset( $_SERVER[ "HTTP_X_REQUESTED_WITH" ] ) );
            
        } // IsAjaxRequest()
        
        
        /**
         * Called from Connect(), responsible for calling the method of the controller
         * routed from the URI
         * 
         * @returns void
         */
        protected function InvokeAction()
        {
            // is_callable() is used over method_exists() in order to properly utilize __call()
            
            if( !empty( $this->oRequest->sAction ) && 
                is_callable( array( $this->oRequest->oController, $this->oRequest->sAction ) ) )
            {
                // Call $oController->$sAction() with arguments $aArguments:
                call_user_func_array( array( $this->oRequest->oController, $this->oRequest->sAction ), 
                    $this->oRequest->aArguments );
            }
            else if( empty( $this->oRequest->sAction ) )
            {
                // Default to the index file:
                $this->oRequest->oController->index();
            }
            else
            {
                // Action is not callable, throw an error:
                return( $this->HandleError( ErrorHandler::ACTION_NOT_FOUND ) );
            }
            
            $this->oRequest->aLoadedData = &$this->oRequest->oController->aData;
        
        } // InvokeAction()
        
        
        /**
         * Called from Connect(), responsible for loading any view file
         * 
         * @returns void
         */
        protected function LoadView()
        {               
            if( $this->oRequest->oController->Is404Error() )
            {
                return( $this->HandleError( ErrorHandler::FILE_NOT_FOUND ) );
            }
            else if( !empty( $this->oRequest->oController->sView ) )
            {
                if( $this->bRequireViewFiles )
                {                     
                    extract( $this->oRequest->oController->aData );

                    if( !empty( $this->oRequest->oController->sLayout ) )
                    {
                        require( $this->oRequest->oController->sLayout );
                    }
                    else if( !empty( $this->sDefaultLayout ) )
                    {
                        require( $this->sDefaultLayout );
                    }
                }
            }
        
        } // LoadView()
        
        
        /**
         * Called from the layout file to load the action specific view file into the layout.
         * 
         * @returns void
         */
        public function GetContent()
        {
            extract( $this->oRequest->oController->aData );

            if( ( $sView = FileFunctions::FileExistsInPath( $this->oRequest->oController->sView ) ) !== false )
            {
                $this->oRequest->sView = $sView;
                require( $sView );
            }
            else
            {
                $this->HandleError( ErrorHandler::VIEW_NOT_FOUND );
            }
            
        } // GetContent()
        
        
        /**
         * Handles errors occurring during the Dispatch process and passes them off to the 
         * defined ErrorController, or throws an exception if the controller does not exist.
         * 
         * @returns void
         */
        protected function HandleError( $sErrorCode )
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
            
        } // HandleError()
        
        
        /**
         * Called to handle a 404 error
         * 
         * @deprecated Use HandleError( ErrorHandler::FILE_NOT_FOUND );
         * @returns void
         */
        protected function Invoke404Error()
        {
            if( !headers_sent() )
            {
                header( "HTTP/1.0 404 Not Found", true, 404 );
            }
            
            $this->HandleError( ErrorHandler::FILE_NOT_FOUND );
        
        } // Invoke404Error()
        
    } // Dispatcher()

?>
