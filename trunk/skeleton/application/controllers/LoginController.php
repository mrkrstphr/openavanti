<?php

	class LoginController extends OpenAvanti\Controller
	{
		
        /**
         *
         *
         */
        public function init()
        {
            $this->view->setLayout("login.phtml");
            
        } // init()
        
        
		/**
		 *
		 */
		public function index()
		{
			$this->view->title = "Login";
			
            $this->view->form = new LoginForm();
            $this->view->form->loadSanitizedPost();
            
		} // index()
		
		public function forgot_password()
		{
			$this->SetData( "title", "Forgot Password" );
			$this->SetView( "login-forgot-password.php" );
		
		} // forgot_password()
		
		
		public function reset_password()
		{
			$this->SetData( "title", "Reset Password" );
			$this->SetView( "login-reset-password.php" );
		
		} // forgot_password()
		
		
		/**
		 *
		 */
		public function validate()
		{
			if(Authenticator::validate())
			{
				$this->redirectTo("/");
			}
			else
			{
                $this->forwardAction("index");
			}
			
		} // validate()
		
		
		/**
		 *
		 */
		public function destroy()
		{
			Authenticator::Destroy();
			
			$this->RedirectTo( "/" );
			
		} // destroy()
	
	} // LoginController()

?>
