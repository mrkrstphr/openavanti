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
class Request 
{
    /**
     *
     */
    private $_request = null;


    /**
     *
     *
     */
    public function __construct()
    {
        $this->_request = curl_init();

    }


    /**
     *
     *
     * @param string $uri
     * @param array $data Optional;
     */
    public function post($uri, $data = array())
    {
        return $this->sendRequest('POST', $uri, $data);
    }


    /**
     * 
     * @param string $method
     * @param string $uri
     * @param array $data Optional; 
     */
    public function sendRequest($method = 'GET', $uri, $data = array())
    {
        curl_setopt($this->_request, CURLOPT_POST, true);
        curl_setopt($this->_request, CURLOPT_URL, $uri);
        curl_setopt($this->_request, CURLOPT_RETURNTRANSFER, true);

        if(!empty($data))
            curl_setopt($this->_request, CURLOPT_POSTFIELDS, $data);
       
        $response = curl_exec($this->_request);

        return new \OpenAvanti\Http\Response($response);
    }

}

