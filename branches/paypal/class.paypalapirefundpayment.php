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
	class PayPalAPIRefundPayment extends PayPalAPI
	{		
		private $oLastError = null;
		private $sLastError = "";
		
		private $oProfile = null;
		
		protected $sType = "";
		
		public $sTransactionID = "";
		public $sNote = "";
        public $sRefundType = "";
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
		protected function Refund()
		{
			$oRefundRequest =& PayPal::getType( "RefundTransactionRequestType" );
			
			if( PayPal::isError( $oRefundRequest ) ) 
			{
				$this->oLastError = $oRefundRequest;
				$this->ParseErrors();
			   	return( false );
			}
			
			$oRefundRequest->setTransactionID( $this->sTransactionID );
            $oRefundRequest->setRefundType( $this->sRefundType );
            $oRefundRequest->setAmount( $this->fAmount );
			$oRefundRequest->setNote( $this->sNote ); 			
			
			return( $this->Request( "RefundTransaction", $oRefundRequest );
			
		} // Payment()
			
	}; // PayPalAPIVoidPayment()

?>
