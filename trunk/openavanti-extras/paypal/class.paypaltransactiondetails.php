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
    class PayPalTransactionDetails extends PayPalNVP
    {
        
        /**
         * 
         * 
         */
        public function __construct( $sEnvironment, $sUserName, $sPassword, $sSignature )
        {
            $this->sMethod = "GetTransactionDetails";
            
            $this->aDefinition = array(
                "transaction_id" => array(
                    "field" => "TRANSACTIONID",
                    "required" => true,
                    "maxlength" => 19
                )
            );
            
            parent::__construct( $sEnvironment, $sUserName, $sPassword, $sSignature );
            
        } // __construct()
        
    } // PayPalTransactionDetails()
    
?>
