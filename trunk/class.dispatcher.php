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
 * @version			0.05
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
	
		/**
		 * Routes the specified request to an associated controller and action. Loads
		 * any specified view file through _SESSION[ "view" ]		 
		 * 
		 * @argument string The current request URI
		 * @returns void
		 */
		public static function Connect( $sRequest )
		{
			$aRequest = explode( "/", $sRequest );
			$_SESSION[ "last-request" ] = $aRequest;
						
			$sController = isset( $aRequest[ 0 ] ) ? 
				$aRequest[ 0 ] . "Controller" : "";
				
			$sAction = isset( $aRequest[ 1 ] ) ? 
				$aRequest[ 1 ] : "";
				
			$iID = isset( $aRequest[ 2 ] ) ? 
				intval( $aRequest[ 2 ] ) : null;
			
			$oController = null;
			
			if( !empty( $sController ) && class_exists( $sController, true ) )
			{
				$oController = new $sController();
								
				self::InvokeAction( $oController, $sAction, $iID );
			}
			else
			{
				$_SESSION[ "view" ] = "404.php";
			}		
			
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
		private static function InvokeAction( &$oController, $sAction, $iID )
		{
			// is_callable() is used over method_exists() in order to properly utilize __call()
			
			if( !empty( $sAction ) && is_callable( array( $oController, $sAction ) ) )
			{
				$aArguments = $_SESSION[ "last-request" ]; // prepare array of arguments
				
				array_shift( $aArguments ); // shift off the controller
				array_shift( $aArguments ); // shift off the action
				
				// call $oController->$sAction() with arguments $aArguments:
				
				call_user_func_array( array( $oController, $sAction ), $aArguments );
			}
			else if( empty( $sAction ) )
			{
				$oController->index();
			}
			else
			{
				$_SESSION[ "view" ] = "404.php";
			}
		
		} // InvokeAction()
		
		
		/**
		 * Called from Connect(), responsible for loading any view file specified in
		 * _SESSION[ "view" ]		 	  		 		 		 		 		 
		 * 
		 * @returns void
		 */
		private static function LoadView( &$oController )
		{
			if( isset( $_SESSION[ "view" ] ) )
			{
				if( !self::IsAjaxRequest() )
				{
					require( "header.php" );
				}
			
				$aData = &$oController->aData;
			
				if( ( $sView = FileFunctions::FileExistsInPath( $_SESSION[ "view" ] ) ) !== false )
				{
					require( $sView );
				}
				else
				{
					require( "404.php" );
				}
				
				if( !self::IsAjaxRequest() )
				{
					require( "footer.php" );
				}
				
				unset( $_SESSION[ "view" ] );
			}
		
		} // LoadView()
		
	
	}; // Dispatcher()

?>
