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


    /**
     * 
     *
     * @category    PayPal
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/paypal
     */  
    class PayPalNVP
    {
        // Constants used across many transaction types: 
        
        const PaymentActionAuthorization = "Authorization";
        const PaymentActionSale = "Sale";
        
        
        const CardTypeVisa = "Visa";
        const CardTypeMasterCard = "MasterCard";
        const CardTypeDiscover = "Discover"; 
        const CardTypeAmericanExpress = "Amex"; 
        
        // A usable array of card types with the PayPal expected card name format:
        
        public static $aCardTypes = array(
            self::CardTypeVisa => "Visa", 
            self::CardTypeMasterCard => "MasterCard", 
            self::CardTypeDiscover => "Discover", 
            self::CardTypeAmericanExpress => "AmericanExpress"
        );
        
        const SandboxURI = "https://api-3t.sandbox.paypal.com/nvp";
        const LiveURI = "https://api-3t.paypal.com/nvp";

        protected $aEnvironmentURIs = array(
            "sandbox" => self::SandboxURI,
            "live" => self::LiveURI
        );
        
        protected $sEnvironment = "sandbox";
        
        protected $sVersion = "56";
        protected $sUserName = "";
        protected $sPassword = "";
        protected $sSignature = "";
        
        protected $sMethod = "";
        
        protected $aDefinition = array();
        protected $aRequestData = array();

        public $aErrors = array();


        /**
         *
         * 
         */
        public function __construct( $sEnvironment, $sUserName, $sPassword, $sSignature )
        {
            if( !isset( $this->aEnvironmentURIs[ strtolower( $sEnvironment ) ] ) )
            {
                throw new Exception( "Unknown environment: {$sEnvironment}" );
            }
            
            $this->sEnvironment = $sEnvironment;
            
            $this->sUserName = $sUserName;
            $this->sPassword = $sPassword;
            $this->sSignature = $sSignature; 
        
        } // __construct()
        
        
        /**
         *
         *
         */
        public function __set( $sVar, $sValue )
        {            
            if( !isset( $this->aDefinition[ $sVar ] ) )
            {
                throw new Exception( "Unknown value {$sVar}" );
            }
            
            $this->aRequestData[ $this->aDefinition[ $sVar ][ "field" ] ] = $sValue;
            
        } // __set()
        
        
        /**
         * 
         *
         */
        protected function Validate()
        {
            // Loop each field in the definition:
            
            foreach( $this->aDefinition as $sExternalKey => $aField )
            {
                $sField = ucwords( str_replace( "_", " ", $sExternalKey ) );
                $sValue = $this->aRequestData[ $aField[ "field" ] ];
                
                // Required:
                
                if( isset( $aField[ "required" ] ) && $aField[ "required" ] === true )
                {
                    if( empty( $sValue ) &&
                        ( !isset( $aField[ "default" ] ) || empty( $aField[ "default" ] ) ) )
                    {
                        $this->aErrors[] = "{$sField} is a required field.";
                    }
                }
                
                // Type:
                
                if( !empty( $sValue ) && isset( $aField[ "type" ] ) && !empty( $aField[ "type" ] ) )
                {
                    switch( strtolower( $aField[ "type" ] ) )
                    {
                        case "carddate":
                            if( strlen( $sValue ) != 6 || strval( $sValue ) != strval( doubleval( $sValue ) ) )
                            {
                                $this->aErrors[] = "{$sField} is not a valid card date.";
                            }
                            else
                            {
                                $sMonth = substr( $sValue, 0, 2 );
                                $sYear = substr( $sValue, 2 );
                                
                                if( $sMonth < 1 || $sMonth > 12 )
                                {
                                    $this->aErrors[] = "{$sField} is not a valid card date.";                                    
                                }
                                else if( $sYear < date( "Y" ) || $sYear > date( "Y", strtotime( "+10 years" ) ) )
                                {
                                    $this->aErrors[] = "{$sField} is not a valid card date.";
                                }
                                else
                                {
                                    $sExpDate = date( "Ymd", strtotime( "{$sMonth}/01/{$sYear}" ) );
                                    $sToday = date( "Ymd" );
                                    
                                    if( $sExpDate < $sToday )
                                    {
                                        $this->aErrors[] = "{$sField} is an expired card date.";
                                    }
                                }
                            }
                        break;
                        
                        case "double":
                            if( strval( $sValue ) != strval( doubleval( $sValue ) ) )
                            {
                                $this->aErrors[] = "{$sField} is not a floating point number.";
                            }
                        break;
                        
                        case "integer":
                            if( strval( $sValue ) != strval( intval( $sValue ) ) )
                            {
                                $this->aErrors[] = "{$sField} is not an integer number.";
                            }                        
                        break;
                        
                        case "utcdate":
                        
                        break;
                        
                        default:
                            throw new Exception( "Unknown type validation requested: " . $aField[ "type" ] );
                    }
                }
            }
            
            return( count( $this->aErrors ) == 0 );
            
        } // Validate()
        
        
        /**
         *
         */ 
        public function Process()
        {
            if( !$this->Validate() )
            {
                return( false );
            }
            
            $rCURL = curl_init();
            
            curl_setopt( $rCURL, CURLOPT_URL, $this->aEnvironmentURIs[ strtolower( $this->sEnvironment ) ] );
            curl_setopt( $rCURL, CURLOPT_VERBOSE, 1 );
            
            curl_setopt( $rCURL, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $rCURL, CURLOPT_SSL_VERIFYHOST, false );
            
            curl_setopt( $rCURL, CURLOPT_RETURNTRANSFER,1 );
            curl_setopt( $rCURL, CURLOPT_POST, 1 );

            $sPostString = "VERSION=" . urlencode( $this->sVersion ) . 
                "&PWD=" . urlencode( $this->sPassword ) . 
                "&USER=" . urlencode( $this->sUserName ) . 
                "&SIGNATURE=" . urlencode( $this->sSignature ) . $nvpStr . 
                "&METHOD={$this->sMethod}";
                
            foreach( $this->aDefinition as $sKey => $aField )
            {
                $sValue = !empty( $this->aRequestData[ $aField[ "field" ] ] ) ? 
                    $this->aRequestData[ $aField[ "field" ] ] : 
                    ( isset( $aField[ "default" ] ) && !empty( $aField[ "default" ] ) ? 
                        $aField[ "default" ] : "" );
                
                if( empty( $sValue ) )
                {
                    continue;
                }
                
                $sPostString .= "&" . $aField[ "field" ] . "=" . urlencode( $sValue );
            }

            curl_setopt( $rCURL, CURLOPT_POSTFIELDS, $sPostString );

            $sResponse = curl_exec( $rCURL );
            
            if( curl_errno( $rCURL ) )
            {
                $this->aErrors[] = "CURL ERROR (" . curl_errno( $rCURL ) . ") " . 
                    curl_error( $rCURL );
                    
                return( false );
            }
            else
            {
                curl_close( $rCURL );
            }
            
            $aResponse = explode( "&", $sResponse );
            
            $aData = array();
            
            foreach( $aResponse as $iKey => &$sValue )
            {
                $aValue = explode( "=", $sValue, 2 );                                
                $aData[ $aValue[ 0 ] ] = urldecode( $aValue[ 1 ] );
            }
            
            if( $aData[ "ACK" ] == "Failure" )
            {
                $i = 0; 
                
                while( true )
                {
                    if( !isset( $aData[ "L_LONGMESSAGE{$i}" ] ) )
                    {
                        break;
                    }
                    
                    $this->aErrors[] = $aData[ "L_SEVERITYCODE{$i}" ] . "(" . 
                        $aData[ "L_ERRORCODE{$i}" ] . ") " . $aData[ "L_LONGMESSAGE{$i}" ];
                    
                    $i++;
                }
            }
            
            return( count( $this->aErrors ) == 0 ? $aData : false );
        
        } // Request()
        
                
        /**
         *
         */ 
        protected function IsError()
        {
            return( count( $this->aErrors ) > 0 );
            
        } // IsError()                        
        
                
        /**
         *
         */ 
        public function GetErrors()
        {
            return( $this->aErrors );        
        
        } // GetErrors()
    
    } // PayPalNVPAPI()

?>
