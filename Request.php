<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5.3+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2012, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.3.0-beta
 */

namespace OpenAvanti;

/**
 * The request object stores information about the web request and how it 
 * was routed, as well as stores data setup by the controller, including 
 * view file and loaded data.
 */
class Request
{
    /**
     * The URI as requested.
     * 
     * @var string $_uri
     */
    protected $_uri = null;
    
    /**
     * The rewritten URI after any routing rules were applied.
     * 
     * @var string $_rewrittenUri
     */
    protected $_rewrittenUri = null;
    
    /**
     * The name of the module handing this request.
     * 
     * @var string $_moduleName
     */
    protected $_moduleName = null;
    
    /**
     * The name of the controller handling this request.
     * 
     * @var string $_controllerName
     */
    protected $_controllerName = null;
    
    /**
     * The name of the action handing this request.
     * 
     * @var string $_actionName
     */
    protected $_actionName = null;
    
    /**
     * Arguments passed along in the URI to the action method.
     * 
     * @var array $_arguments
     */
    protected $_arguments = array();
    
    /**
     * The type of request (GET|POST).
     * 
     * @var string $_requestType
     */
    protected $_requestType = null;
    
    /**
     * Stores whether this is a secure (HTTPS) request.
     * 
     * @var boolean $_secureConnection
     */
    protected $_secureConnection = false;
    
    
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
     * Determines whether or not the current HTTP request came via AJAX.                                             
     * 
     * @return boolean True of the request is via AJAX, false otherwise 
     */
    public static function isAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }
    
    /**
     * Stores the original URI for this request
     *
     * @param string $uri
     * @return \OpenAvanti\Request
     */
    public function setUri($uri)
    {
        $this->_uri = $uri;
        return $this;
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
     * Stores the rewritten URI by any routing rules
     *
     * @param string $uri
     * @return \OpenAvanti\Request
     */
    public function setRewrittenUri($uri)
    {
        $this->_rewrittenUri = $uri;
        return $this;
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
     * Sets the name of the module that is handling this request
     * 
     * @param string $module
     * @return \OpenAvanti\Request
     */
    public function setModuleName($module)
    {
        $this->_moduleName = $module;
        return $this;
    }
    
    /**
     * Returns the name of the module that is handling this request
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->_moduleName;
    }
    
    /**
     * Stores the name of the controller that is handling this request
     * 
     * @param string $controller
     * @return \OpenAvanti\Request
     */
    public function setControllerName($controller)
    {
        $this->_controllerName = $controller;
        return $this;
    }
    
    /**
     * Returns the controller component of the request from the URI
     * 
     * @return string The controller part of the request
     */
    public function getControllerName()
    {
        return $this->_controllerName;
    }
    
    /**
     * Sets the name of the action for this request
     *
     * @param string $action
     * @return \OpenAvanti\Request
     */
    public function setActionName($action)
    {
        $this->_actionName = $action;
        return $this;
    }
    
    /**
     * Returns the action component of the request from the URI
     * 
     * @return string The action part of the request
     */
    public function getActionName()
    {
        return $this->_actionName;
    }
    
    /**
     * Stores the arguemnts sent in the Uri
     *
     * @param array $arguments
     * @return \OpenAvanti\Request
     */
    public function setArguments(array $arguments)
    {
        $this->_arguments = $arguments;
        return $this;
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
    
    /**
     * Gets a param from either the $_POST (first priority) or $_GET
     *
     * @param string $name The name of the parameter to search for
     * @param string $default The default value if no param is found
     * @return string
     */
    public function getParam($name, $default = '')
    {
        if (isset($_POST[$name])) {
            return $_POST[$name];
        } else if (isset($_GET[$name])) {
            return $_GET[$name];
        }
        
        return $default;
    }
}
