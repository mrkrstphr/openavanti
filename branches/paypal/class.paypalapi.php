<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	None
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link			http://www.openavanti.com
 * @version			0.6.4-alpha
 *
 */
 

	/**
	 * 
	 *
	 * @category	PayPal
	 * @author		Kristopher Wilson
	 * @link		http://www.openavanti.com/docs/paypal
	 */	 
	class PayPalAPI
	{		
		private $oLastError = null;
		private $sLastError = "";
		
		private $oProfile = null;
		
		
		/**
		 *
		 */		 		
		public function __construct( $sEnvironment, $sUserName, $sPassword, $sSignature )
		{		
			$oHandler =& ProfileHandler_Array::getInstance(
				array(
					"username" => $sUserName,
		            "certificateFile" => null,
		            "subject" => null,
		            "environment" => $sEnvironment 
				)
			);
		            
			$iPID = ProfileHandler::generateID();
		   
		   	$this->oProfile = & new APIProfile( $iPID, $oHandler );
		   	          
		   	$this->oProfile->setAPIUsername( $sUserName );
		   	$this->oProfile->setAPIPassword( $sPassword ); 
		   	$this->oProfile->setSignature( $sSignature );
		   	$this->oProfile->setEnvironment( $sEnvironment );		
		
		} // __construct()
		
		
		/**
		 *
		 */	
		public function Request( $sType, $oRequest )
		{
			$oCaller =& PayPal::getCallerServices( $this->oProfile );
			$this->oResponse = $oCaller->{$sType}( $oRequest );
			
			$sAck = $this->oResponse->getAck();
			
			switch( $sAck ) 
			{
				case "Success":
			   	case "SuccessWithWarning":
			    	return( true );
				break;
			}
			
			$this->IsError( $this->oResponse );
			
			return( false );
		
		} // Request()
		
				
		/**
		 *
		 */	
		protected function IsError( &$oObject )
		{
			if( PayPal::isError( $oObject ) )
			{
				$this->oLastError = $oObject;
				$this->ParseErrors();
				
			  	return( true );
			}
			
			return( false );
			
		} // IsError()
		
				
		/**
		 *
		 */	
		public function GetError()
		{
			return( $this->sLastError );		
		
		} // GetError()
		
				
		/**
		 *
		 */	
		private function ParseErrors()
		{
			if( !is_null( $this->oLastError ) && PayPal::isError( $this->oLastError ) || 
				!empty( $this->oLastError->Errors ) )
			{
				if( get_class( $this->oLastError ) == "SOAP_Fault" )
				{
					$this->sLastError = $this->oLastError->message . ": " . 
						$this->oLastError->userinfo;
					return;
				}
				
				$oErrorList = $this->oLastError->GetErrors();
								
				if( !is_array( $oErrorList ) ) 
				{
					$this->sLastError .= $oErrorList->getErrorCode() . ": " . 
						$oErrorList->getLongMessage() . "\n";
				} 
				else 
				{
			      foreach( $oErrorList as $oError ) 
					{
						$this->sLastError .= $oError->getErrorCode() . ": " . 
							$oError->getLongMessage() . "\n";
			      }
			   }
			}
		
		} // ParseErrors()
	
	}; // PayPalAPI()

?>
