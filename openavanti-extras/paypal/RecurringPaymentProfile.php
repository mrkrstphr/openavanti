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

/**
 * 
 *
 * @category    PayPal
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/paypal
 */  
class RecurringPaymentProfile extends Nvp
{
    
    /**
     * 
     * 
     */
    public function init()
    {
        $this->_method = "UpdateRecurringPaymentsProfile";
        
        $this->_definition = array(
            "profile_id" => array(
                "field" => "PROFILEID",
                "required" => true,
                "maxlength" => 19
            ),
            "note" => array(
                "field" => "NOTE",
                "required" => false
                //"maxlength" => ??
            ),
            "description" => array(
                "field" => "DESC",
                "required" => false,
                "maxlength" => 127
            ),
            "subscriber_name" => array(
                "field" => "SUBSCRIBERNAME",
                "required" => false,
                "maxlength" => 32
            ),
            "profile_reference" => array(
                "field" => "PROFILEREFERENCE",
                "required" => false,
                "maxlength" => 127
            ),
            "additional_billing_cycles" => array(
                "field" => "ADDITIONALBILLINGCYCLES",
                "required" => false,
                "type" => integer
            ),
            "amount" => array(
                "field" => "AMT",
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
            "outstanding_amount" => array(
                "field" => "OUTSTANDINGAMT", 
                "required" => false,
                "type" => "double"
            ),
            "auto_bill_amount" => array(
                "field" => "AUTOBILLAMT",
                "required" => false
            ),
            "max_failed_payments" => array(
                "field" => "MAXFAILEDPAYMENTS",
                "required" => false,
                "type" => "integer"
            ),
            "profile_start_date" => array(
                "field" => "PROFILESTARTDATE",
                "required" => false,
                "type" => "utcdate"
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
            
            "billing_period" => array(
                "field" => "BILLINGPERIOD",
                "required" => false
            ),
            "billing_frequency" => array(
                "field" => "BILLINGFREQUENCY",
                "required" => false,
                "type" => "integer"
            ),
            "total_billing_cycles" => array(
                "field" => "TOTALBILLINGCYCLES",
                "required" => false,
                "type" => "integer"
            ),
            "amount" => array(
                "field" => "AMT",
                "required" => false,
                "type" => "double"
            ),
            "trial_billing_period" => array(
                "field" => "TRIALBILLINGPERIOD",
                "required" => false
            ),
            "trial_billing_frequency" => array(
                "field" => "TRIALBILLINGFREQUENCY",
                "required" => false,
                "type" => "integer"
            ),
            "trial_total_billing_cycles" => array(
                "field" => "TRIALTOTALBILLINGCYCLES",
                "required" => false,
                "type" => "integer"
            ),
            "currency" => array(
                "field" => "CURRENCYCODE", 
                "default" => "USD",
                "required" => false
            ),
            
            "card_type" => array(
                "field" => "CREDITCARDTYPE", 
                "required" => false
            ),
            "card_number" => array(
                "field" => "ACCT", 
                "required" => false
            ),
            "card_exp_date" => array(
                "field" => "EXPDATE", 
                "type" => "carddate",
                "required" => false
            ),
            "card_cvv2" => array(
                "field" => "CVV2", 
                "required" => false
            ),
            
            "email" => array(
                "field" => "EMAIL", 
                "required" => false,
                "maxlength" => 127
            ),
            
            "first_name" => array(
                "field" => "FIRSTNAME", 
                "required" => false,
                "maxlength" => 25
            ),
            "last_name" => array(
                "field" => "LASTNAME", 
                "required" => false,
                "maxlength" => 25
            ),
            
            "street1" => array(
                "field" => "STREET", 
                "required" => false,
                "maxlength" => 100
            ),
            "street2" => array(
                "field" => "STREET2", 
                "required" => false,
                "maxlength" => 100
            ),
            "city" => array(
                "field" => "CITY", 
                "required" => false,
                "maxlength" => 40
            ),
            "state" => array(
                "field" => "STATE", 
                "required" => false,
                "maxlength" => 40
            ),
            "zip" => array(
                "field" => "ZIP", 
                "required" => false,
                "maxlength" => 20
            ),
            "country" => array(
                "field" => "COUNTRYCODE",
                "required" => false, 
                "maxlength" => 2
            ),
            "phone" => array(
                "field" => "PHONENUM",
                "required" => false,
                "maxlength" => 20
            ),
        );
        
        
        
    } // init()
    
} // RecurringPaymentProfile()

?>
