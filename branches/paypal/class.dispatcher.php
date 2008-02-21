<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link				http://www.openavanti.com
 * @version			0.6.4-alpha
 *
 */
 
 
	/**
	 * Dispatcher to route URI request to appropriate controller / method
	 *
	 * @category	Database
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/dispatcher
	 */
	class Dispatcher
	{
		private static $aRoutes = array();
		private static $bRequireViewFiles = true;
		
		private static $s404ViewFile = "404.php";
		
		private static $x404Callback = null;
		
		private static $sHeaderFile = "header.php";
		private static $sFooterFile = "footer.php";
		
		
		
		/**
		 *   		 		 		 		 		 
		 * 
		 */
		private function __construct()
		{
			// this class cannot be instantiated
			
		} // __construct()
		
		
		/**
		 *   		 		 		 		 		 
		 * 
		 * @returns void
		 */
		public static function RequireViewFiles( $bRequireViewFiles )
		{
			self::$bRequireViewFiles = $bRequireViewFiles;
			
		} // RequireViewFiles()
		
		
		/**
		 *   		 		 		 		 		 
		 * 
		 * @returns void
		 */
		public static function Set404View( $sView )
		{
			self::$s404ViewFile = $sView;
			
		} // Set404View()
		
		
		/**
		 *   		 		 		 		 		 
		 * 
		 * @returns void
		 */
		public static function SetHeaderView( $sView = "" )
		{
			self::$sHeaderFile = $sView;
			
		} // SetHeaderView()
		
		
		/**
		 *   		 		 		 		 		 
		 * 
		 * @returns void
		 */
		public static function SetFooterView( $sView = "" )
		{
			self::$sFooterFile = $sView;
			
		} // SetFooterView()
		
		
		/**
		 *   		 		 		 		 		 
		 * 
		 * @returns void
		 */
		public static function Set404Handler( $xCallback )
		{
			self::$x404Callback = $xCallback;
			
		} // Set404Handler()


		/**
		 * 		  
		 * 
		 * @argument string 
		 * @argument string 
		 * @returns void
		 */
        public function AddRoute( $sPattern, $sReplacement )
        {
            self::$aRoutes[] = array(
                "pattern" => $sPattern,
                "replace" => $sReplacement
            );
        }
	
	
		/**
		 * Routes the specified request to an associated controller and action. Loads
		 * any specified view file stored in the controller		 
		 * 
		 * @argument string The current request URI
		 * @returns void
		 */
		public static function Connect( $sRequest )
		{
			$oRequest = new Request();
			$oRequest->sURI = $sRequest;
			
			$sController = "";
			$sAction = "";
			$aArguments = array();
			
			// Load an empty controller. This may be replaced if we found a controller through a route.
			
			$oRequest->oController = new Controller();
			
			// Loop each stored route and attempt to find a match to the URI:
			
			foreach( self::$aRoutes as $aRoute )
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
			
			$oRequest->sRewrittenURI = $sRequest;
			
			
			// Explode the request on /
			$aRequest = explode( "/", $sRequest );
			
			// Store this as the last request:
			$_SESSION[ "last-request" ] = $aRequest;
			
			$oRequest->sControllerName = count( $aRequest ) > 0 ? 
				array_shift( $aRequest ) . "Controller" : "";
			
			$oRequest->sAction = count( $aRequest ) > 0 ? array_shift( $aRequest ) : "";
			$oRequest->aArguments = !empty( $aRequest ) ? $aRequest : array();
				
			
			// If we've found a controller and the class exists:
			if( !empty( $oRequest->sControllerName ) && 
				class_exists( $oRequest->sControllerName, true ) )
			{
				// Replace our empty controller with the routed one:				
				$oRequest->oController = new $oRequest->sControllerName();
				
				// Attempt to invoke an action on this controller: 				
				self::InvokeAction( $oRequest ); //->oController, $sAction, $aArguments );
			}
			else
			{
				// If we can't find the controller, we must throw a 404 error:
				$oRequest->oController->Set404Error();
			}		
			
			// Continue on with the view loader method which will put the appropriate presentation
			// on the screen:
			
			self::LoadView( $oRequest );
		
			return( $oRequest );
		
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
		private static function InvokeAction( Request &$oRequest ) //&$oController, $sAction, $aArguments )
		{
			// is_callable() is used over method_exists() in order to properly utilize __call()
			
			if( !empty( $oRequest->sAction ) && 
				is_callable( array( $oRequest->oController, $oRequest->sAction ) ) )
			{
				// Call $oController->$sAction() with arguments $aArguments:
				call_user_func_array( array( $oRequest->oController, $oRequest->sAction ), 
					$oRequest->aArguments );
			}
			else if( empty( $oRequest->sAction ) )
			{
				// Default to the index file:
				$oRequest->oController->index();
			}
			else
			{
				// Action is not callable, throw a 404 error:
				$oRequest->oController->Set404Error();
			}
		
		} // InvokeAction()
		
		
		/**
		 * Called from Connect(), responsible for loading any view file
		 * 
		 * @returns void
		 */
		private static function LoadView( Request &$oRequest ) //&$oController )
		{				
			if( $oRequest->oController->Is404Error() )
			{
				self::Invoke404Error();
			}
			else if( !empty( $oRequest->oController->sView ) )
			{
				if( self::$bRequireViewFiles )
				{
					$aData = &$oRequest->oController->aData;
			
					if( !self::IsAjaxRequest() && isset( self::$sHeaderFile ) )
					{
						require( self::$sHeaderFile );
					}
				
					if( ( $sView = FileFunctions::FileExistsInPath( $oRequest->oController->sView ) ) !== false )
					{
						require( $sView );
					}
					else
					{
						self::Invoke404Error();
					}
					
					
					if( !self::IsAjaxRequest() && isset( self::$sFooterFile ) )
					{
						require( self::$sFooterFile );
					}
				}
			}
		
		} // LoadView()
		
		
		/**
		 *   		 		 		 		 		 
		 * 
		 * @returns void
		 */
		private static function Invoke404Error()
		{
			if( isset( self::$x404Callback ) ) 
			{
				if( is_callable( self::$x404Callback ) )
				{					
					call_user_func_array( 
						self::$x404Callback, 
						array( 
							implode( "/", $_SESSION[ "last-request" ] ), 
							isset( $_SERVER[ "HTTP_REFERER" ] ) ? $_SERVER[ "HTTP_REFERER" ] : "" 
						) 
					);
				}
			}
			else if( isset( self::$s404ViewFile ) )
			{
				header( "HTTP/1.0 404 Not Found", true, 404 );
					
				if( ( $sView = FileFunctions::FileExistsInPath( self::$s404ViewFile ) ) !== false )
				{					
					if( !self::IsAjaxRequest() && isset( self::$sHeaderFile ) )
					{
						require( self::$sHeaderFile );
					}
					
					require( $sView );
					
					if( !self::IsAjaxRequest() && isset( self::$sFooterFile ) )
					{
						require( self::$sFooterFile );
					}
				}
			}
		
		} // Invoke404Error()
		
	
	}; // Dispatcher()

?>
