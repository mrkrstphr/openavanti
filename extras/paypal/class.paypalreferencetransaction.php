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
    class PayPalReferenceTransaction extends PayPalNVP
    {
        
        /**
         * 
         * 
         */
        public function __construct( $sEnvironment, $sUserName, $sPassword, $sSignature )
        {
            $this->sMethod = "DoReferenceTransaction";
            
            $this->aDefinition = array(
                "transaction_id" => array(
                    "field" => "REFERENCEID",
                    "required" => true,
                    "maxlength" => 19
                ),
                "payment_action" => array(
                    "field" => "PAYMENTACTION",
                    "default" => self::PaymentActionSale,
                    "required" => true
                ),
                "soft_descriptor" => array(
                    "field" => "SOFTDESCRIPTOR", 
                    "required" => false,
                    "maxlength" => 22
                ),
                
                "shipping_name" => array(
                    "field" => "SHIPTONAME",
                    "required" => false,
                    "maxlength" => 32
                ),
                "shipping_street1" => array(
                    "field" => "SHIPTOSTREET",
                    "required" => false,
                    "maxlength" => 100
                ),
                "shipping_street2" => array(
                    "field" => "SHIPTOSTREET2",
                    "required" => false,
                    "maxlength" => 100
                ),
                "shipping_city" => array(
                    "field" => "SHIPTOCITY",
                    "required" => false,
                    "maxlength" => 40
                ),
                "shipping_state" => array(
                    "field" => "SHIPTOSTATE",
                    "required" => false,
                    "maxlength" => 40
                ),
                "shipping_zip" => array(
                    "field" => "SHIPTOZIP",
                    "required" => false,
                    "maxlength" => 20
                ),
                "shipping_country_code" => array(
                    "field" => "SHIPTOCOUNTRYCODE",
                    "required" => false,
                    "maxlength" => 2
                ),
                "shipping_phone" => array(
                    "field" => "SHIPTOPHONENUM",
                    "required" => false,
                    "maxlength" => 20
                ),
                
                
                "amount" => array(
                    "field" => "AMT", 
                    "type" => "double",
                    "required" => true
                ),
                "currency" => array(
                    "field" => "CURRENCYCODE", 
                    "default" => "USD",
                    "required" => false,
                    "maxlength" => 3
                ),
                "item_amount" => array(
                    "field" => "ITEMAMT", 
                    "type" => "double",
                    "required" => false
                ),
                "shipping_amount" => array(
                    "field" => "SHIPPINGAMT", 
                    "type" => "double",
                    "required" => false
                ),
                "insurance_amount" => array(
                    "field" => "INSURANCEAMT", 
                    "type" => "double",
                    "required" => false
                ),
                "shipping_discount" => array(
                    "field" => "SHIPPINGDISCOUNT", 
                    "type" => "double",
                    "required" => false
                ),
                "handling_amount" => array(
                    "field" => "HANDLINGAMT", 
                    "type" => "double",
                    "required" => false
                ),
                "tax_amount" => array(
                    "field" => "TAXAMT", 
                    "type" => "double",
                    "required" => false
                ),
                "description" => array(
                    "field" => "DESC", 
                    "required" => false,
                    "maxlength" => 127
                ),
                "custom" => array(
                    "field" => "CUSTOM", 
                    "required" => false,
                    "maxlength" => 256
                ),
                "invoice_number" => array(
                    "field" => "INVNUM", 
                    "required" => false,
                    "maxlength" => 127
                ),
                "button_source" => array(
                    "field" => "BUTTONSOURCE", 
                    "required" => false,
                    "maxlength" => 32
                ),
                "notify_url" => array(
                    "field" => "NOTIFYURL", 
                    "required" => false,
                    "maxlength" => 2048
                )
            );
            
            parent::__construct( $sEnvironment, $sUserName, $sPassword, $sSignature );
            
        } // __construct()
        
    } // PayPalCapture()
    
?>
