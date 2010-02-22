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

namespace OpenAvanti\PayPal;

// TODO: UNTESTED

/**
 * 
 *
 * @category    PayPal
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/paypal
 */  
class NonReferencedCredit extends Nvp
{

    /**
     * 
     * 
     */
    public function init()
    {
        $this->_method = "DoNonReferencedCredit";
        
        $this->_definition = array(
            "amount" => array(
                "field" => "AMT",
                "required" => true,
                "type" => "double"
            ),
            "net_amount" => array(
                "field" => "NETAMT",
                "required" => false,
                "type" => "double"
            ),
            "shipping_amount" => array(
                "field" => "SHIPPINGAMT",
                "required" => false,
                "type" => "double"
            ),
            "tax_amount" => array(
                "field" => "TAXAMT",
                "required" => false,
                "type" => "double"
            ),
            "currency_code" => array(
                "field" => "CURRENCYCODE",
                "default" => "USD",
                "required" => true
            ),
            "note" => array(
                "field" => "NOTE",
                "required" => false
                //"maxlength" => ???
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
                "required" => true,
                "maxlength" => 25
            ),
            "last_name" => array(
                "field" => "LASTNAME",
                "required" => true,
                "maxlength" => 25
            ),
            "email" => array(
                "field" => "LASTNAME",
                "required" => false,
                "maxlength" => 127
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
            )
        );
        
        
        
    } // init()
    
} // NonReferencedCredit()

?>
