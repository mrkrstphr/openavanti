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
class RefundTransaction extends Nvp
{
    
    const RefundTypeFull = "Full";
    const RefundTypePartial = "Partial";
    const RefundTypeOther = "Other";
    
    /**
     * 
     * 
     */
    public function init()
    {
        $this->_method = "RefundTransaction";
        
        $this->_definition = array(
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
        
        
        
    } // init()
    
} // RefundTransaction()

?>
