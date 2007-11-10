<?php

	/////////////////////////////////////////////////////////////////////////////////////////////////
	class AuthenticatedDispatcher extends Dispatcher
	//
	// Description:
	//		Extends the standard dispatcher and wraps it with an authentication protocol. Using
	// 	the AuthenticatedDispatcher, every dispatched request will require the user to be
	//		logged in.
	//
	{
		protected $sLoginURI = "";
		protected $sLoginView = "";
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function __construct( $sLoginURI, $sLoginView )
		//
		// Description:
		//		Constructor. Takes two arguments:
		//
		//			sLoginURI - The URI to redirect to if the user is not logged in
		//			sLoginView - The view file to display to the user when login is requested
		//
		{
			parent::__construct();
			
			$this->sLoginURI = &$sLoginURI;
			$this->sLoginView = &$sLoginView;
			
		} // __construct()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function connect()
		//
		// Description:
		//		Wraps the dispatcher's connect() method and checks for a valid login before allowing
		//		the dispatcher to route requests.
		//
		{			
			$oLogin = new Login();
			
			if( $this->sModel == "login" )
			{				
				if( $this->sAction == "logout" )
				{
					$oLogin->logout();
					
					header( "Location: /" );
				}
				elseif( $this->sAction == "process" ) // && !$oLogin->process() )
				{							
					if( !$oLogin->process() )
					{						
						header( "Location: {$this->sLoginURI}" );
					}
					else
					{						
						if( isset( $_SESSION[ "last_requested_uri" ] ) )
						{
							$sLastRequestedURI = $_SESSION[ "last_requested_uri" ];
							unset( $_SESSION[ "last_requested_uri" ] );
							
							header( "Location: {$sLastRequestedURI}" );
						}
						else
						{
							header( "Location: /" );
						}
					}
				}
				else
				{
					
					require( $this->sLoginView );
					exit;
				}
			}
			else if( !$oLogin->validate() )
			{				
				$_SESSION[ "last_requested_uri" ] = $_SERVER[ "SCRIPT_URI" ];
				
				header( "Location: {$this->sLoginURI}" );
			}
			
			parent::connect();	
		
		} // connect()
		
	
	}; // AuthenticatedDispatcher()

?>
