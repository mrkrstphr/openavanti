<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	PayPalAPI
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
	class PayPalAPIVoidPayment extends PayPalAPI
	{		
		private $oLastError = null;
		private $sLastError = "";
		
		private $oProfile = null;
		
		protected $sType = "";
		
		public $sAuthorizationNumber = "";
		public $sNote = "";
		
		
		/**
		 *
		 */	
		public function __construct( $sEnvironment, $sUserName, $sPassword, $sSignature )
		{
			parent::__construct( $sEnvironment, $sUserName, $sPassword, $sSignature );	
		
		} // __construct()
		
		
		/**
		 *
		 */	
		protected function Void()
		{
			$oVoidRequest =& PayPal::getType( "DoVoidRequestType" );
			
			if( PayPal::isError( $oVoidRequest ) ) 
			{
				$this->oLastError = $oCaptureRequest;
				$this->ParseErrors();
			   	return( false );
			}
			
			$oVoidRequest->setAuthorizationID( $this->sAuthorizationNumber ); 
			$oVoidRequest->setNote( $this->sNote ); 			
			
			return( $this->Request( "DoVoid", $oVoidRequest );
			
		} // Payment()
			
	}; // PayPalAPIVoidPayment()

?>
