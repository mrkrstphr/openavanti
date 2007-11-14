<?php

	////////////////////////////////////////////////////////////////////////////////////////////////
	class Dispatcher
	{
	
		////////////////////////////////////////////////////////////////////////////////////////////
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
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		private static function InvokeAction( &$oController, $sAction, $iID )
		{
			// is_callable() is used over method_exists() in order to properly utilize __call()
			
			if( !empty( $sAction ) && is_callable( array( $oController, $sAction ) ) )
			{				
				$oController->$sAction( $iID );
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
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		private static function LoadView( &$oController )
		{
			if( isset( $_SESSION[ "view" ] ) )
			{
				if( !isset( $_SERVER[ "HTTP_X_REQUESTED_WITH" ] ) )
				{
					require( "header.php" );
				}
			
				$aData = &$oController->aData;
			
				require( $_SESSION[ "view" ] );
				
				
				if( !isset( $_SERVER[ "HTTP_X_REQUESTED_WITH" ] ) )
				{
					require( "footer.php" );
				}
				
				unset( $_SESSION[ "view" ] );
			}
		
		} // LoadView()
		
	
	}; // Dispatcher()

?>
