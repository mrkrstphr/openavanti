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
class TransactionDetails extends Nvp
{
    
    /**
     * 
     * 
     */
    public function init()
    {
        $this->_method = "GetTransactionDetails";
        
        $this->_definition = array(
            "transaction_id" => array(
                "field" => "TRANSACTIONID",
                "required" => true,
                "maxlength" => 19
            )
        );
        
    } // init()
    
} // TransactionDetails()

?>
