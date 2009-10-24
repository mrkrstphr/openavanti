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
    class PayPalCapture extends PayPalNVP
    {
        const CompleteTypeComplete = "Complete";
        const CompleteTypeNotComplete = "NotComplete";
        
        /**
         * 
         * 
         */
        public function __construct( $sEnvironment, $sUserName, $sPassword, $sSignature )
        {
            $this->sMethod = "DoCapture";
            
            $this->aDefinition = array(
                "transaction_id" => array(
                    "field" => "AUTHORIZATIONID",
                    "required" => true,
                    "maxlength" => 19
                ),
                "amount" => array(
                    "field" => "AMT", 
                    "required" => true
                ),
                "currency" => array(
                    "field" => "CURRENCYCODE", 
                    "default" => "USD",
                    "required" => false,
                    "maxlength" => 3
                ),
                "complete_type" => array(
                    "field" => "COMPLETETYPE", 
                    "default" => self::CompleteTypeComplete,
                    "required" => true,
                    "maxlength" => 12
                ),
                "invoice_number" => array(
                    "field" => "INVNUM", 
                    "required" => false,
                    "maxlength" => 127
                ),
                "note" => array(
                    "field" => "NOTE", 
                    "required" => false,
                    "maxlength" => 255
                ),
                "soft_descriptor" => array(
                    "field" => "SOFTDESCRIPTOR", 
                    "required" => false,
                    "maxlength" => 22
                )
            );
            
            parent::__construct( $sEnvironment, $sUserName, $sPassword, $sSignature );
            
        } // __construct()
        
    } // PayPalCapture()
    
?>
