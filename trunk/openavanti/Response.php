<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */


/**
 * 
 *
 * @category    Response
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/response
 */
class Response
{
    // Stores whether or not the request resulted in a 404 error:
    protected $_is404Error = false;
    
    protected $_headers = array();
    
    
    /**
     * Adds a header to this object. Name and value will be combined, 
     * separated by a colon. 
     * 
     * @argument string The name of the header
     * @argument string The value of the header
     * @argument bool Optional; Should this header replace a previous 
     *      similar one? Default: false
     * @returns void
     */
    public function setHeader($header, $value, $replace = false)
    {
        $this->_headers[$header] = array(
            "header" => $header, 
            "value" => $value, 
            "replace" => $replace
        );
        
    } // setHeader()
    
    
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
     * @argument array An array of header data to add
     * @returns void
     */
    public function setHeaders(array $headers)
    {
        $this->_headers += $headers;
        
    } // setHeaders()
    
    
    /**
     * Returns the specified header, if it exists
     * 
     * @argument string The header to retrieve
     * @returns array The specified header
     */
    public function getHeader($header)
    {
        if(isset($this->_headers[$header]))
        {
            return $this->_headers[$header];
        }
        
        return null;
        
    } // getHeader()
    
    
    /**
     * Returns all headers added to this object
     * 
     * @returns array The array of headers added to this object
     */
    public function getHeaders()
    {
        return $this->_headers;
        
    } // getHeaders()
    
    
    /**
     * Clears the specified header if it is stored in this object
     * 
     * @argument string The header to remove
     * @returns void
     */
    public function clearHeader($header)
    {
        if(isset($this->_headers[$header]))
        {
            unset($this->_headers[$header]);
        }
        
    } // clearHeader()
    
    
    /**
     * Clears all headers stored in this object
     * 
     * @returns void
     */
    public function clearHeaders()
    {
        $this->_headers = array();
        
    } // clearHeaders()
    
    
    /**
     * Sends all headers added to the Response object
     * 
     * @returns bool True if headers were sent, false otherwise
     */
    public function sendHeaders()
    {
        if(headers_sent())
        {
            return false;
        }
        
        foreach($this->_headers as $name => $header)
        {
            header($name . ": " . $header["value"], $header["replace"]);
        }
        
        return true;
        
    } // sendHeaders()
    
    
    /**
     * Sets the HTTP status code header. This method will only work if no output or headers
     * have already been sent.
     *       
     * @argument int The HTTP status code
     * @returns bool True if the operation was successful, false on failure
     */
    public function setHTTPStatus($code)
    {
        if(!headers_sent())
        {
            header(" ", true, $code);
            
            return true;
        }
        
        return false;
        
    } // setHTTPStatus()
    
    
    /**
     * This specialized method does two things: it attempts to set the HTTP status code,
     * 400 by default, to inform the web browser that there was an error, and second, 
     * echoes the supplied error message to the browser, which could be a simple string or
     * a JSON object.                
     *
     * @argument string The error message to output
     * @argument int The response code to send to the browser, default: 400
     * @returns void                         
     */                     
    public function ajaxError($errorString, $responseCode = 400)
    {
        $this->setHTTPStatus($responseCode);
        
        echo $errorString;
        
    } // ajaxError()
    
    
    /**
     * Returns the internal 404 status to determine if a 404 error flag was triggered
     *
     * @returns bool True if a 404 error was encountered, false otherwise
     */                             
    public function is404Error()
    {
        return $this->_is404Error;
        
    } // is404Error()
    
    
    /**
     * Sets or clears the internal 404 error status flag. 
     * 
     * @argument bool True to trigger a 404 error, false to clear the 404 flag, default: true
     * @returns void
     */ 
    public function set404Error($is404Error = true)
    {
        $this->_is404Error = $is404Error;
        
    } // set404Error()


} // Response()

?>
