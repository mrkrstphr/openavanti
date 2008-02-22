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
	class PayPalAPICapture extends PayPalAPI
	{		
		private $oLastError = null;
		private $sLastError = "";
		
		private $oProfile = null;
		
		public $sAuthorizationID = "";
		
		public $sCurrencyID = "";
		public $fAmount = 0.00;
		
		
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
		public function Capture()
		{
			$oCaptureRequest =& PayPal::getType( "DoCaptureRequestType" );
			
			if( PayPal::isError( $oCaptureRequest ) ) 
			{
			   $this->oLastError = $oCaptureRequest;
				$this->ParseErrors();
			   return( false );
			}
			
			
			$oCaptureRequest->setAuthorizationID( $sAuthorizationID, "iso-8859-1" );
			$oCaptureRequest->setCompleteType( "Complete" );
			
			$oAmtType =& PayPal::getType( "BasicAmountType" );
			$oAmtType->setattr( "currencyID", $this->sCurrencyID );
			$oAmtType->setval( $this->fAmount, "iso-8859-1" );
			
			$oCaptureRequest->setAmount( $oAmtType );
			
			return( $this->Request( "DoCapture", $oCaptureRequest );
					
		} // Capture()
		
	}; // PayPalAPICapture()

?>
