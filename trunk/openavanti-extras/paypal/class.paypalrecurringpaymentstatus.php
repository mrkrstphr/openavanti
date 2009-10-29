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
    class PayPalRecurringPaymentStatus extends PayPalNVP
    {
        const ActionCancel = "Cancel";
        const ActionSuspend = "Suspend";
        const ActionReactivate = "Reactivate";
        
        /**
         * 
         * 
         */
        public function __construct( $sEnvironment, $sUserName, $sPassword, $sSignature )
        {
            $this->sMethod = "ManageRecurringPaymentsProfileStatus";
            
            $this->aDefinition = array(
                "profile_id" => array(
                    "field" => "PROFILEID",
                    "required" => true,
                    "maxlength" => 19
                ),
                "action" => array(
                    "field" => "ACTION",
                    "required" => true,
                    "maxlength" => 19
                ),
                "note" => array(
                    "field" => "NOTE",
                    "required" => false
                    //"maxlength" => ??
                )
            );
            
            parent::__construct( $sEnvironment, $sUserName, $sPassword, $sSignature );
            
        } // __construct()
        
    } // PayPalRecurringPaymentStatus()
    
?>
