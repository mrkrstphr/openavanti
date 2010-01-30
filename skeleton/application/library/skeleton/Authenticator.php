<?php
	
	class Authenticator
	{
		public static $user = null;
	
        /**
         *
         *
         */
		public static function authenticate(Request &$request)
		{
			if(!Authenticator::confirm())
			{
                if($request->_controllerName != "LoginController")
                {
                    $request->_controllerName = "LoginController";
                    $request->_actionName = "index";
                }
			}
		
		} // authenticate()
	
	
        /**
         *
         *
         */
		public static function validate()
		{
			if(isset($_POST["email"]) && isset($_POST["password"]))
			{
				$user = new User();
				
				$user->find(array(
					"where" => "email_address = '" . addslashes($_POST["email"]) . "' " . 
                        "AND password = '" . hash('sha256', $_POST["password"]) . "' "
				) );
                
				if(count($user->Count()) > 0)
				{					
					$user->login_key = md5(microtime());
                    
					if(!$user->saveLoginKey())
                    {
                        Validation::SetError("email", "Failed to update user account.");
                        return false;
                    }
					
					Authenticator::LoadUserInfo($user);
					
					return true;
				}
				else
				{
					Validation::SetError("email", "The e-mail address and/or password you entered " . 
						"is not valid.");
				}
			}
			
			return false;
			
		} // validate()
	
	
        /**
         *
         *
         */
		private static function Confirm()
		{
			if( isset( $_SESSION[ "uid" ] ) && isset( $_SESSION[ "lkey" ] ) )
			{
				$oUser = new User();
				
				$oUser->find( array(
					"where" => "user_id = " . intval($_SESSION[ "uid" ]) . " AND " . 
						"login_key = '" . addslashes( $_SESSION[ "lkey" ] ) . "' "
				) );
				
                //echo "user_id = " . intval($_SESSION[ "uid" ]) . " AND " . 
				//		"login_key = '" . addslashes( $_SESSION[ "lkey" ] ) . "' ";
                
				if( $oUser->Count() )
				{
					Authenticator::LoadUserInfo( $oUser->Current() );
					
					return( true );
				}
			}
			
			return( false );
		
		} // Confirm()
	
	
        /**
         *
         *
         */
		public static function Destroy()
		{
			unset( $_SESSION[ "uid" ], $_SESSION[ "lkey" ] );
			
		} // Destroy()
	
	
        /**
         *
         *
         */
		private static function loadUserInfo($user)
		{
			$_SESSION[ "uid" ] = $user->user_id;
			$_SESSION[ "lkey" ] = $user->login_key;
			
			self::$user = $user->getRecord();
			
		} // loadUserInfo()
	
	} // Authenticator()

?>
