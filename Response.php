<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2012, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.3.0-beta
 */

namespace OpenAvanti;

/**
 * 
 */
class Response
{
    /**
     * Stores whether or not the request resulted in a 404 error.
     *
     * @var boolean $_is404Error
     */
    protected $_is404Error = false;
    
    /**
     * Stores the response headers being sent to the browser.
     *
     * @var boolean $_headers
     */
    protected $_headers = array();
    
    /**
     * Adds a header to this object. Name and value will be combined, 
     * separated by a colon. 
     * 
     * @param string $header The name of the header
     * @param string $value The value of the header
     * @param bool $replace Should this header replace a previous 
     *      similar one? Default: false
     * @return \OpenAvanti\Response
     */
    public function setHeader($header, $value, $replace = false)
    {
        $this->_headers[$header] = array(
            'header' => $header, 
            'value' => $value, 
            'replace' => $replace
        );
        
        return $this;
    }
    
    /**
     * Adds an array of headers to the Request's headers. Each element of
     * the array should be in the format of: 
     * 
     *   array(
     *       header => The name of the header
     *       value => The value of the header
     *       replace => optional: Should this header replace a previous 
     *          similar one? Default: false
     *   )
     * 
     * @param array $headers An array of header data to add
     * @return \OpenAvanti\Response
     */
    public function setHeaders(array $headers)
    {
        $this->_headers += $headers;
        return $this;
    }
    
    /**
     * Returns the specified header, if it exists
     * 
     * @param string $header The header to retrieve
     * @return array The specified header
     */
    public function getHeader($header)
    {
        if (isset($this->_headers[$header])) {
            return $this->_headers[$header];
        }
        
        return null;
    }
    
    /**
     * Returns all headers added to this object
     * 
     * @return array The array of headers added to this object
     */
    public function getHeaders()
    {
        return $this->_headers;
    }
    
    /**
     * Clears the specified header if it is stored in this object
     * 
     * @param string $header The header to remove
     */
    public function clearHeader($header)
    {
        if(isset($this->_headers[$header]))
            unset($this->_headers[$header]);
    }
    
    /**
     * Clears all headers stored in this object
     *
     * @return \OpenAvanti\Response
     */
    public function clearHeaders()
    {
        $this->_headers = array();
        return $this;
    }
    
    /**
     * Sends all headers added to the Response object
     * 
     * @return bool True if headers were sent, false otherwise
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            return false;
        }
        
        foreach ($this->_headers as $name => $header) {
            header($name . ': ' . $header['value'], $header['replace']);
        }
        
        return true;
    }
    
    /**
     * Sets the HTTP status code header. This method will only work if no output or headers
     * have already been sent.
     *       
     * @param int $code The HTTP status code
     * @return bool True if the operation was successful, false on failure
     */
    public function setHttpStatus($code)
    {
        if (!headers_sent()) {
            header(' ', true, $code);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * This specialized method does two things: it attempts to set the HTTP status code,
     * 400 by default, to inform the web browser that there was an error, and second, 
     * echoes the supplied error message to the browser, which could be a simple string or
     * a JSON object.                
     *
     * @param string $errorString The error message to output
     * @param int $responseCode The response code to send to the browser, default: 400
     */                     
    public function ajaxError($errorString, $responseCode = 400)
    {
        $this->setHttpStatus($responseCode);
        
        echo $errorString;
    }
    
    /**
     * Returns the internal 404 status to determine if a 404 error flag was triggered
     *
     * @return bool True if a 404 error was encountered, false otherwise
     */                             
    public function is404Error()
    {
        return $this->_is404Error;
    }
    
    /**
     * Sets or clears the internal 404 error status flag. 
     * 
     * @param bool $is404Error True to trigger a 404 error, false to clear the
     *      404 flag, default: true
     * @return \OpenAvanti\Response
     */ 
    public function set404Error($is404Error = true)
    {
        $this->_is404Error = $is404Error;
        return $this;
    }
}

