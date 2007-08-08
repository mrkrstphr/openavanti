<?php

	/////////////////////////////////////////////////////////////////////////////////////////////////
	class login
	{
	
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function __construct()
		{
	
		} // __construct()
		
	
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function validate()
		{	
			if( isset( $_SESSION[ 'user' ]->user_id ) && !empty( $_SESSION[ 'user' ]->user_id ) )
			{
				return( true );
			}
			
			return( $this->process() );
		
		} // validate()
		
	
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function process()
		{		
			if( isset( $_POST[ "persons" ][ "email" ] ) && 
				isset( $_POST[ "users" ][ "password" ] ) )
			{				
				$user = new cruder( "users", null, array(
					"person.email" => addslashes( $_POST[ "persons" ][ "email" ] ),
					"users.password" => md5( $_POST[ "users" ][ "password" ] )	
				) );
									
				if( !empty( $user->user_id ) )
				{
					$_SESSION[ 'user' ] = new StdClass();
					
					foreach( $user as $field => $value )
					{
						if( !is_object( $value ) || $field == "person" )
						{
							if( strpos( $field, "." ) )
							{
								$fields = explode( ".", $field );
								
								if( !isset( $_SESSION[ 'user' ]->$fields[0] ) )
								{
									$_SESSION[ 'user' ]->$fields[0] = new StdClass();
								}
								
								$_SESSION[ 'user' ]->$fields[0]->$fields[1] = $value;
							}
							else
							{
								$_SESSION[ 'user' ]->$field = $value;
							}
						}
					}
				
					return( true );
				
				
				}
			}
			
			
			
			return( false );
		
		} // process()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function logout()
		{
			if( isset( $_SESSION[ 'user' ] ) )
			{
				unset( $_SESSION[ 'user' ] );
			}
			
			return( false );
		
		} // logout()
	
	}  // Login()

?>
