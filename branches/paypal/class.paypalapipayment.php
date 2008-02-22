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
	class PayPalAPIPayment extends PayPalAPI
	{		
		private $oLastError = null;
		private $sLastError = "";
		
		private $oProfile = null;
		
		protected $sType = "";
		
		public $sCurrencyID = "";
		public $fAmount = 0.00;

		public $sFirstName = "";
		public $sLastName = "";
		public $sStreet1 = "";
		public $sStreet2 = "";
		public $sCityName = "";
		public $sState = "";
		public $sPostalCode = "";
		
		public $sEmailAddress = "";
		public $sContactPhone = "";

		public $sCreditCardType = "";
		public $sCreditCardNumber = "";
		
		public $sCreditCardExpMonth = "";
		public $sCreditCardExpYear = "";
		
		public $sCreditCardCVV2 = "";
		
		
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
		protected function Payment()
		{
			$oDirectPaymentRequest =& PayPal::getType( "DoDirectPaymentRequestType" );
			
			if( PayPal::isError( $oDirectPaymentRequest ) )
			{
			   $this->oLastError = $oDirectPaymentRequest;
				$this->ParseErrors();
			   return( false );
			}

			//
			// Build BasicAmountType object:
			//			

			$oTotal =& PayPal::getType( "BasicAmountType" );
			$oTotal->setattr( "currencyID", $this->sCurrencyID );
			$oTotal->setval( $this->fAmount, "iso-8859-1" );

			//
			// Build AddressType object:
			//
			
			$oShipTo =& PayPal::getType( "AddressType" );
			
			$oShipTo->setName( $this->sFirstName . " " . $this->sLastName );
			$oShipTo->setStreet1( $this->sStreet1 );
			$oShipTo->setStreet2( $this->sStreet2 );
			$oShipTo->setCityName( $this->sCityName );
			$oShipTo->setStateOrProvince( $this->sState );
			$oShipTo->setCountry( "US" ); // US only supported
			$oShipTo->setPostalCode( $this->sPostalCode );

			//
			// Build PersonNameType object:
			//
			
			$oPerson =& PayPal::getType( "PersonNameType" );
			$oPerson->setFirstName( $this->sFirstName );
			$oPerson->setLastName( $this->sLastName );


			//
			// Build PayerInfoType object:
			//
	
			$oPayer =& PayPal::getType( "PayerInfoType" );
			$oPayer->setPayerName( $oPerson );
			$oPayer->setPayer( $this->sEmailAddress );
			$oPayer->setContactPhone( $this->sContactPhone );

			//
			// Build CreditCardDetailsType object:
			//
			
			$oCard =& PayPal::getType( "CreditCardDetailsType" );
			
			$oCard->setCreditCardType( $this->sCreditCardType );
			$oCard->setCreditCardNumber( $this->sCreditCardNumber );
			
			$oCard->setExpMonth( $this->sCreditCardExpMonth );
			$oCard->setExpYear( $this->sCreditCardExpYear );
			
			$oCard->setCVV2( $this->sCreditCardCVV2 );
			
			$oCard->setCardOwner( $oPayer );

			// 
			// Build PaymentDetailsType object:
			//

			$oPaymentDetails =& PayPal::getType( "PaymentDetailsType" );
			$oPaymentDetails->setOrderTotal( $oTotal );
			$oPaymentDetails->setShipToAddress( $oShipTo );

			//
			// Build DoDirectPaymentRequestDetailsType object:
			//
			
			$oDirectPaymentDetails =& PayPal::getType( "DoDirectPaymentRequestDetailsType" );
			$oDirectPaymentDetails->setPaymentDetails( $oPaymentDetails );
			$oDirectPaymentDetails->setCreditCard( $oCard );
			
			$oDirectPaymentDetails->setIPAddress( $_SERVER[ "SERVER_ADDR" ] );
			$oDirectPaymentDetails->setPaymentAction( $this->sType );
			
			$oDirectPaymentRequest->setDoDirectPaymentRequestDetails( $oDirectPaymentDetails );
			
			
			return( $this->Request( "DoDirectPayment", $oDirectPaymentRequest );
			
		} // Payment()
			
	}; // PayPalAPIPayment()

?>
