<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson <kwilson@shuttlebox.net>
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @package         openavanti 
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */

namespace OpenAvanti\Http;

/**
 * An HTTP client implementation for making requests to remote web servers.
 *
 * @category    Controller
 * @author      Kristopher Wilson
 * @package     openavanti
 * @link        http://www.openavanti.com/documentation/1.4.0/Http/Request
 */
class Response 
{

    /**
     *
     */
    private $_response = null;

    /**
     *
     */
    public $body = '';


    /**
     *
     *
     * @param string $response
     */
    public function __construct($response)
    {
        $this->_response = $response;

        $matches = array();

        preg_match_all('#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims', $response, $matches);
        
        $headerBlock = array_pop($matches[0]);
        $headers = explode("\r\n", str_replace("\r\n\r\n", '', $headerBlock));
        
        $this->body = str_replace($headerBlock, '', $response);
    }

}

