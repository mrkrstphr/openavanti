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
	class PayPalAPISale extends PayPalAPIPayment
	{		
		private $oLastError = null;
		private $sLastError = "";
		
		private $oProfile = null;
		
				
		/**
		 *
		 */	
		public function __construct( $sEnvironment, $sUserName, $sPassword, $sSignature )
		{
			parent::__construct( $sEnvironment, $sUserName, $sPassword, $sSignature );		
		
			$this->sType = "Sale";
		
		} // __construct()
		
			
	}; // PayPalAPISale()

?>
