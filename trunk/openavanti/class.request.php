<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @package         openavanti
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 */

    /**
     * The request object stores information about the web request and how it was routed, as well
     * as stores data setup by the controller, including view file and loaded data.
     *
     * @category    Database
     * @author      Kristopher Wilson
     * @package     openavanti
     * @link        http://www.openavanti.com/documentation/docs/1.0.3/Request
     */
    class Request
    {
        /**
         * The URI of the Request
         */
        public $sURI = null;
        
        /**
         * The URI after custom route rules have written the original
         */
        public $sRewrittenURI = null;
        
        /**
         * The name of the controller handling this request
         */
        public $sControllerName = null;
        
        /**
         * A copy of the controller handling this request
         */
        public $oController = null;
        
        /**
         * The controller action method being invoked for this request
         */
        public $sAction = null;
        
        /**
         * Arguments supplied with this request that are passed to the action method
         */
        public $aArguments = array();
        
        /**
         * Data loaded by the controller and utilized by the view file
         */
        public $aLoadedData = array();
        
        /**
         * The name of the view file loaded for this request
         */
        public $sView = null;
        
        /**
         * The request type (GET, POST, etc)
         */
        public $sRequestType = "";
        
        /**
         * Is this connection secure (HTTPS)?
         */
        public $bSecureConnection = false;
        
        
        /**
         * Constructor. Determines information about the request type and connection type and 
         * stores it within the class.       
         *
         */
        public function __construct()
        {
            $this->sRequestType = $_SERVER[ "REQUEST_METHOD" ];
            $this->bSecureConnection = isset( $_SERVER[ "HTTPS" ] ) && !empty( $_SERVER[ "HTTPS" ] );
        
        } // __construct()
        
        
        /**
         * Returns true if the current request came via a secure connection, or false otherwise.
         *
         * @return bool True if the current request is a secure connection, false otherwise
         */                             
        public function IsSecureConnection()
        {
            return( $this->bSecureConnection );
            
        } // IsSecureConnection()
        
        
        /**
         * Returns true if the current request is a POST request, or false otherwise.
         *
         * @return bool True if the current request is a POST request, false otherwise
         */                             
        public function IsPostRequest()
        {
            return( strtolower( $sRequestType ) == "post" );
            
        } // IsSecureConnection()
        
        
        /**
         * Returns true if the current request is a GET request, or false otherwise.
         *
         * @return bool True if the current request is a GET request, false otherwise
         */                             
        public function IsGetRequest()
        {
            return( strtolower( $sRequestType ) == "get" );
            
        } // IsSecureConnection()
        
        
    } // Request()

?>
