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

namespace OpenAvanti;

/**
 * The request object stores information about the web request and how it 
 * was routed, as well as stores data setup by the controller, including 
 * view file and loaded data.
 *
 * @category    Response
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/request
 */
class Request
{
    public $_uri = null;
    public $_rewrittenUri = null;
    
    public $_controllerName = null;
    public $_actionName = null;
    
    public $_arguments = array();
    
    public $_requestType = "";
    
    public $_secureConnection = false;
    
    
    /**
     * Constructor. Determines information about the request type and 
     * connection type and stores it within the class.
     */
    public function __construct()
    {
        $this->_requestType = strtolower($_SERVER['REQUEST_METHOD']);
        
        $this->_secureConnection = isset($_SERVER['HTTPS']) && 
            !empty($_SERVER['HTTPS']);
    }
    
    
    /**
     * Returns true if the current request came via a secure connection, or 
     * false otherwise.
     *
     * @return bool True if the current request is a secure connection, 
     *      false otherwise
     */                             
    public function isSecureConnection()
    {
        return $this->_secureConnection;
    }
    
    
    /**
     * Returns true if the current request is a POST request, or false 
     * otherwise.
     *
     * @return bool True if the current request is a POST request, false 
     *      otherwise
     */                             
    public function isPostRequest()
    {
        return strtolower($this->_requestType) == 'post';
    }
    
    
    /**
     * Returns true if the current request is a GET request, or false 
     * otherwise.
     *
     * @return bool True if the current request is a GET request, false 
     *      otherwise
     */                             
    public function isGetRequest()
    {
        return strtolower($this->_requestType) == 'get';
    }
    
    
    /**
     *
     *
     */
    public function isPostEmpty()
    {
        return empty($_POST);
    }
    
    
    /**
     *
     *
     */
    public function isGetEmpty()
    {
        return empty($_GET);
    }
    
    
    /**
     * Determines whether or not the current HTTP request came via AJAX.                                             
     * 
     * @return boolean True of the request is via AJAX, false otherwise 
     */
    public static function isAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }


    /**
     * Returns the requested URI as it was passed to the server
     * 
     * @return string The requested URI
     */
    public function getUri()
    {
        return $this->_uri;
    }
    
    
    /**
     * Returns the requested URI after any user rewrites are performed
     * through the dispatching process
     * 
     * @return string The requested URI after any user rewrites are 
     *      performed
     */
    public function getRewrittenUri()
    {
        return $this->_rewrittenUri;
    }


    /**
     * Returns the controller component of the request from the URI
     * 
     * @return string The controller part of the request
     */
    public function getController()
    {
        return $this->_controllerName;
    }
    

    /**
     * Returns the action component of the request from the URI
     * 
     * @return string The action part of the request
     */
    public function getAction()
    {
        return $this->_actionName;
    }
    

    /**
     * Returns all arguments of the request from the URI
     * 
     * @return string The arguments of the request
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

}


