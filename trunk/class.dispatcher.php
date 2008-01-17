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
		 * Adds a custom route based on matching the URI to a regular express. On a sucessful match,
		 * the supplied controller is instantiated and it's method action is invoked, passing
		 * along the individual parts of the URI as parameters		 		  
		 * 
		 * @argument string The regular expression to match against the URI
		 * @argument string The controller to instantiate on a successful match
		 * @argument string The action to invoke against the controller on a succesful match
		 * @returns void
		 */
/*		public function AddRoute( $sPreg, $sController, $sAction )
		{
			self::$aRoutes[] = array(
				"match" => $sPreg,
				"controller" => $sController,
				"action" => $sAction
			);
			
		} // AddRoute()
*/

        public function AddRout( $sPregMatch, $sPregReplacement )
        {
            self::$aRoutes[] = array(
                "match" => $sPregMatch,
                "replace" => $sPregReplace
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
			$bRouteFound = false;
			
			$sController = "";
			$sAction = "";
			$aArguments = array();
			
			// Load an empty controller. This may be replaced if we found a controller through a route.
			
			$oController = new Controller();
			
			// Loop each stored route and attempt to find a match to the URI:
			
			foreach( self::$aRoutes as $aRoute )
			{
				/*
				// If the request matches this route:
				if( preg_match( $aRoute[ "match" ], $sRequest ) )
				{
					$sController = $aRoute[ "controller" ] . "Controller";
					$sAction = $aRoute[ "action" ];
					
					// If the controller specified by the route exists:
					if( !empty( $sController ) && class_exists( $sController, true ) )
					{
						$oController = new $sController();
						
						// Invoke the action of the controller:
						self::InvokeAction( $oController, $sAction, $aArguments );
					}
					else
					{
						// The controller does not exist, we must invoke a 404:
						$oController->Set404Error();
					}
					
					$bRouteFound = true;
				}
				*/
			}
			
			// If we did not found a match to the supplied routes, try to find a match for the standard
			// route, ie: controller/action[/args]:
			
			if( !$bRouteFound )
			{
				// Explode the request on /
				$aRequest = explode( "/", $sRequest );
				
				// Store this as the last request:
				$_SESSION[ "last-request" ] = $aRequest;
				
				$sController = count( $aRequest ) > 0 ? array_shift( $aRequest ) . "Controller" : "";
				
				$sAction = count( $aRequest ) > 0 ? array_shift( $aRequest ) : "";
				$aArguments = !empty( $aRequest ) ? $aRequest : array();
			}
			
			// If we've found a controller and the class exists:
			if( !empty( $sController ) && class_exists( $sController, true ) )
			{
				// Replace our empty controller with the routed one:
				$oController = new $sController();
				
				// Attempt to invoke an action on this controller: 				
				self::InvokeAction( $oController, $sAction, $aArguments );
			}
			else
			{
				// If we can't find the controller, we must throw a 404 error:
				$oController->Set404Error();
			}		
			
			// Continue on with the view loader method which will put the appropriate presentation
			// on the screen:
			
			self::LoadView( $oController );
		
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
		private static function InvokeAction( &$oController, $sAction, $aArguments )
		{
			// is_callable() is used over method_exists() in order to properly utilize __call()
			
			if( !empty( $sAction ) && is_callable( array( $oController, $sAction ) ) )
			{
				// Call $oController->$sAction() with arguments $aArguments:
				call_user_func_array( array( $oController, $sAction ), $aArguments );
			}
			else if( empty( $sAction ) )
			{
				// Default to the index file:
				$oController->index();
			}
			else
			{
				// Action is not callable, throw a 404 error:
				$oController->Set404Error();
			}
		
		} // InvokeAction()
		
		
		/**
		 * Called from Connect(), responsible for loading any view file
		 * 
		 * @returns void
		 */
		private static function LoadView( &$oController )
		{				
			if( $oController->Is404Error() )
			{
				self::Invoke404Error();
			}
			else if( !empty( $oController->sView ) )
			{
				if( self::$bRequireViewFiles )
				{
					$aData = &$oController->aData;
			
					if( !self::IsAjaxRequest() && isset( self::$sHeaderFile ) )
					{
						require( self::$sHeaderFile );
					}
				
					if( ( $sView = FileFunctions::FileExistsInPath( $oController->sView ) ) !== false )
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
				else
				{
					$_SESSION[ "data" ] = &$oController->aData;
					$_SESSION[ "view" ] = $oController->sView;
				}
			}
		
		} // LoadView()
		
		
		/**
		 *   		 		 		 		 		 
		 * 
		 * @returns void
		 */
		public static function CleanUp()
		{
			unset( $_SESSION[ "view" ], $_SESSION[ "data" ] );
			
		} // CleanUp()
		
		
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
