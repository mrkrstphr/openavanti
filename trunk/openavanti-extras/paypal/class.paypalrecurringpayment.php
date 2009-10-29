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
    class PayPalRecurringPayment extends PayPalNVP
    {
        const AutoBillAmtNoAutoBill = "NoAutoBill";
        const AutoBillAmtAddToNextBilling = "AddToNextBilling";
        
        const BillingPeriodDay = "Day";
        const BillingPeriodWeek = "Week";
        const BillingPeriodSemiMonth = "SemiMonth";
        const BillingPeriodMonth = "Month";
        const BillingPeriodYear = "Year";
        
        const FailedInitPaymentActionContinue = "ContinueOnFailure";
        const FailedInitPaymentActionCancel = "CancelOnFailure";
        
        /**
         * 
         * 
         */
        public function __construct( $sEnvironment, $sUserName, $sPassword, $sSignature )
        {
            $this->sMethod = "CreateRecurringPaymentsProfile";
            
            $this->aDefinition = array(
                "subscriber_name" => array(
                    "field" => "SUBSCRIBERNAME",
                    "required" => false,
                    "maxlength" => 32
                ),
                "profile_start_date" => array(
                    "field" => "PROFILESTARTDATE",
                    "required" => true,
                    "type" => "utcdate"
                ),
                "profile_reference" => array(
                    "field" => "PROFILEREFERENCE",
                    "required" => false,
                    "maxlength" => 127
                ),
                
                "description" => array(
                    "field" => "DESC",
                    "required" => true,
                    "maxlength" => 127
                ),
                "max_failed_payments" => array(
                    "field" => "MAXFAILEDPAYMENTS",
                    "required" => false,
                    "type" => "integer"
                ),
                "auto_bill_amount" => array(
                    "field" => "AUTOBILLAMT",
                    "required" => false
                ),
                
                "billing_period" => array(
                    "field" => "BILLINGPERIOD",
                    "required" => true
                ),
                "billing_frequency" => array(
                    "field" => "BILLINGFREQUENCY",
                    "required" => true,
                    "type" => "integer"
                ),
                "total_billing_cycles" => array(
                    "field" => "TOTALBILLINGCYCLES",
                    "required" => false,
                    "type" => "integer"
                ),
                "amount" => array(
                    "field" => "AMT",
                    "required" => true,
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
                    "required" => true
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
                
                "initial_amount" => array(
                    "field" => "INITAMT", 
                    "required" => false,
                    "type" => "double"
                ),
                "failed_initial_amount_action" => array(
                    "field" => "FAILEDINITAMTACTION", 
                    "required" => false
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
                
                "email" => array(
                    "field" => "EMAIL", 
                    "required" => false,
                    "maxlength" => 127
                ),
                "business" => array(
                    "field" => "BUSINESS",
                    "required" => false,
                    "maxlength" => 127
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
                
            );
            
            parent::__construct( $sEnvironment, $sUserName, $sPassword, $sSignature );
            
        } // __construct()
        
    } // PayPalRecurringPayment()
    
?>
