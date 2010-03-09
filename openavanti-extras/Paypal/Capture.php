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
class Capture extends Nvp
{
    const CompleteTypeComplete = "Complete";
    const CompleteTypeNotComplete = "NotComplete";
    
    /**
     * 
     * 
     */
    public function init()
    {
        $this->_method = "DoCapture";
        
        $this->_definition = array(
            "transaction_id" => array(
                "field" => "AUTHORIZATIONID",
                "required" => true,
                "maxlength" => 19
            ),
            "amount" => array(
                "field" => "AMT", 
                "required" => true
            ),
            "currency" => array(
                "field" => "CURRENCYCODE", 
                "default" => "USD",
                "required" => false,
                "maxlength" => 3
            ),
            "complete_type" => array(
                "field" => "COMPLETETYPE", 
                "default" => self::CompleteTypeComplete,
                "required" => true,
                "maxlength" => 12
            ),
            "invoice_number" => array(
                "field" => "INVNUM", 
                "required" => false,
                "maxlength" => 127
            ),
            "note" => array(
                "field" => "NOTE", 
                "required" => false,
                "maxlength" => 255
            ),
            "soft_descriptor" => array(
                "field" => "SOFTDESCRIPTOR", 
                "required" => false,
                "maxlength" => 22
            )
        );
        
        
        
    } // init()
    
} // Capture()

?>
