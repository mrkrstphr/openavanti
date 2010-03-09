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
    class SetExpressCheckout extends Nvp
    {
        
        /**
         * 
         * 
         */
        protected function init()
        {
            $this->_method = "SetExpressCheckout";
            
            $this->_definition = array(
                "maxAmount" => array(
                    "field" => "MAXAMT",
                    "required" => false
                ),
                "returnUrl" => array(
                    "field" => "RETURNURL", 
                    "required" => true
                ),
                "cancelUrl" => array(
                    "field" => "CANCELURL", 
                    "required" => true
                ),
                // CALLBACK // TODO
                // CALLBACKTIMEOUT // TODO
                "requestConfirmShipping" => array(
                    "field" => "REQCONFIRMSHIPPING", 
                    "required" => false
                ),
                "noShipping" => array(
                    "field" => "NOSHIPPING", 
                    "required" => false
                ),
                "allowNote" => array(
                    "field" => "ALLOWNOTE", 
                    "required" => false
                ),
                "addrOverride" => array(
                    "field" => "ADDROVERRIDE",
                    "required" => false
                ),
                "localeCode" => array(
                    "field" => "LOCALECODE",
                    "required" => false
                ),
                // PAGESTYLE // TODO
                // HDRIMG // TODO
                // HDRBORDERCOLOR ?/ TODO
                // HDRBACKCOLOR // TODO
                // PAYFLOWCOLOR // TODO
                // PAYMENTACTION // TODO SOON!
                "email" => array(
                    "field" => "EMAIL", 
                    "required" => false,
                    "maxlength" => 127
                ),
                // SOLUTIONTYPE // TODO
                // LANDINGPAGE // TODO SOON?
                // CHANNELTYPE // TODO
                // GIROPAYSUCCESSURL // TODO
                // GIROPAYCANCELURL // TODO
                // BANKTXNPENDINGURL // TODO
                // BRANDNAME // TODO SOON!
                // CUSTOMERSERVICENUMBER // TODO
                // GIFTMESSAGEENABLE // TODO
                // GIFTRECEIPTENABLE // TODO
                // GIFTWRAPENABLE // TODO
                // GIFTWRAPNAME // TODO 
                // GIFTWRAPAMOUNT // TODO
                // BUYEREMAILOPTINENABLE // TODO
                // SURVEYQUESTION // TODO
                // CALLBACKVERSION // TODO
                // SURVEYENABLE // TODO
                // L_SURVEYCHOICEn // TODO
                "amount" => array(
                    "field" => "AMT", 
                    "required" => true,
                ),
                // CURRENCYCODE // TODO
                // ITEMAMT // TODO
                // SHIPPINGAMT // TODO
                // INSURANCEAMT // TODO
                // SHIPDISCAMT // TODO
                // INSURANCEOPTIONOFFERED // TODO
                // HANDLINGAMT // TODO
                // TAXAMT // TODO
                // DESC // TODO SOON!
                // CUSTOM // TODO
                // INVNUM // TODO
                // BUTTONSOURCE // TODO
                // NOTIFYURL // TODO 
                // NOTETEXT // TODO
                // TRANSACTIONID // TODO
                // ALLOWEDPAYMENTMETHOD // TODO
                // Payment Details Item Type Fields // TODO
                // Seller Details Type Fields // TODO
                // Ebay Item Payment Details Item Type  Fields // TODO
                // Buyer Details Fields // TODO
                // FundingSourceDetailsType Fields // TODO
                // Shipping Options Type Fields // TODO
                // Billing Agreement Details Type Fields // TODO
                "billingType" => array(
                    "field" => "L_BILLINGTYPE", 
                    "required" => true,
                    "array" => true
                ),
            );
            
        } // init()
        
    } // SetExpressCheckout()
    
?>
