<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    None
 * @copyright       Copyright (c) 2008, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         0.6.4-alpha
 *
 */


    /**
     * 
     *
     * @category    PayPal
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/paypal
     */  
    class PayPalDirectPayment extends PayPalNVP
    {
        
        /**
         * 
         * 
         */
        public function __construct( $sEnvironment, $sUserName, $sPassword, $sSignature )
        {
            $this->sMethod = "DoDirectPayment";
            
            $this->aDefinition = array(
                "payment_action" => array(
                    "field" => "PAYMENTACTION",
                    "default" => self::PaymentActionSale,
                    "required" => true
                ),
                "ip_address" => array(
                    "field" => "IPADDRESS", 
                    "required" => true
                ),
                "card_type" => array(
                    "field" => "CREDITCARDTYPE", 
                    "required" => true
                ),
                "card_number" => array(
                    "field" => "ACCT", 
                    "required" => true
                ),
                "card_exp_date" => array(
                    "field" => "EXPDATE", 
                    "type" => "carddate",
                    "required" => true
                ),
                "card_cvv2" => array(
                    "field" => "CVV2", 
                    "required" => true
                ),
                "first_name" => array(
                    "field" => "FIRSTNAME",
                    "required" => true
                ),
                "last_name" => array(
                    "field" => "LASTNAME",
                    "required" => true
                ),
                "street1" => array(
                    "field" => "STREET", 
                    "required" => true,
                    "maxlength" => 100
                ),
                "street2" => array(
                    "field" => "STREET2", 
                    "required" => false,
                    "maxlength" => 100
                ),
                "city" => array(
                    "field" => "CITY", 
                    "required" => true,
                    "maxlength" => 40
                ),
                "state" => array(
                    "field" => "STATE", 
                    "required" => true,
                    "maxlength" => 40
                ),
                "zip" => array(
                    "field" => "ZIP", 
                    "required" => true,
                    "maxlength" => 20
                ),
                "country" => array(
                    "field" => "COUNTRYCODE", 
                    "default" => "US",
                    "required" => true, 
                    "maxlength" => 2
                ),
                "phone" => array(
                    "field" => "PHONENUM",
                    "required" => false,
                    "maxlength" => 20
                ),
                "currency" => array(
                    "field" => "CURRENCYCODE", 
                    "default" => "USD",
                    "required" => false
                ),
                "amount" => array(
                    "field" => "AMT", 
                    "type" => "double",
                    "required" => true
                )
            );
            
            parent::__construct( $sEnvironment, $sUserName, $sPassword, $sSignature );
            
        } // __construct()
        
    } // PayPalDirectPayment()
    
?>
