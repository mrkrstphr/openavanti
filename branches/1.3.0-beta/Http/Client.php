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
 * @link        http://www.openavanti.com/documentation/1.4.0/Http/Client
 */
class Client
{
    /**
     *
     */
    private $_request = null;
    
    /**
     *
     */
    private $_response = null;


    /**
     *
     *
     * @param string $server
     * @param string $uri
     * @param array $postData Optional;
     * @param array $headers Optional;
     */
    public function sendPost($server, $uri, $postData = array(), $headers = array())
    {
        $this->_request = new \OpenAvanti\Http\Request();

        foreach($headers as $key => $value)
        {
            $this->_request->setHeader($key, $value);
        }

        $this->_response = $this->_request->post($server . '/' . ltrim($uri, '/'), $postData);

        return $this->_response;
    }

}


