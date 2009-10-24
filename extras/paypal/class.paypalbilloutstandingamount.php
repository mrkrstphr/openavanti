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
    class PayPalBillOutstandingAmount extends PayPalNVP
    {
        
        /**
         * 
         * 
         */
        public function __construct( $sEnvironment, $sUserName, $sPassword, $sSignature )
        {
            $this->sMethod = "BillOutstandingAmount";
            
            $this->aDefinition = array(
                "profile_id" => array(
                    "field" => "PROFILEID",
                    "required" => true,
                    "maxlength" => 19
                ),
                "amount" => array(
                    "field" => "AMT",
                    "required" => false,
                    "type" => "integer"
                ),
                "currency" => array(
                    "field" => "CURRENCYCODE", 
                    "default" => "USD",
                    "required" => true
                ),
                "note" => array(
                    "field" => "NOTE",
                    "required" => false
                    //"maxlength" => ??
                )
            );
            
            parent::__construct( $sEnvironment, $sUserName, $sPassword, $sSignature );
            
        } // __construct()
        
    } // PayPalBillOutstandingAmount()
    
?>
