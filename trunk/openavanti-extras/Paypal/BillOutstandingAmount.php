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
class BillOutstandingAmount extends Nvp
{
    
    /**
     * 
     * 
     */
    public function init()
    {
        $this->_method = "BillOutstandingAmount";
        
        $this->_definition = array(
            "profile_id" => array(
                "field" => "PROFILEID",
                "required" => true,
                "maxlength" => 19
            ),
            "amount" => array(
                "field" => "AMT",
                "required" => false,
                "type" => "integer"
            ),
            "currency" => array(
                "field" => "CURRENCYCODE", 
                "default" => "USD",
                "required" => true
            ),
            "note" => array(
                "field" => "NOTE",
                "required" => false
                //"maxlength" => ??
            )
        );
        
    } // init()
    
} // BillOutstandingAmount()

?>
