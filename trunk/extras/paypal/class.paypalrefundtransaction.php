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
    class PayPalRefundTransaction extends PayPalNVP
    {
        
        const RefundTypeFull = "Full";
        const RefundTypePartial = "Partial";
        const RefundTypeOther = "Other";
        
        /**
         * 
         * 
         */
        public function __construct( $sEnvironment, $sUserName, $sPassword, $sSignature )
        {
            $this->sMethod = "RefundTransaction";
            
            $this->aDefinition = array(
                "transaction_id" => array(
                    "field" => "TRANSACTIONID",
                    "required" => true,
                    "maxlength" => 19
                ),
                "refund_type" => array(
                    "field" => "REFUNDTYPE",
                    "required" => true
                ),
                "amount" => array(
                    "field" => "AMT",
                    "required" => false
                ),
                "note" => array(
                    "field" => "NOTE",
                    "required" => false,
                    "maxlength" => 255
                )
            );
            
            parent::__construct( $sEnvironment, $sUserName, $sPassword, $sSignature );
            
        } // __construct()
        
    } // PayPalCapture()
    
?>
