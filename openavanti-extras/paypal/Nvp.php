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

namespace OpenAvanti\Paypal;

/**
 * 
 *
 * @category    PayPal
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/paypal
 */  
class Nvp
{
    // Constants used across many transaction types: 
    
    const PaymentActionAuthorization = "Authorization";
    const PaymentActionSale = "Sale";
    
    
    const CardTypeVisa = "Visa";
    const CardTypeMasterCard = "MasterCard";
    const CardTypeDiscover = "Discover"; 
    const CardTypeAmericanExpress = "Amex"; 
    
    // A usable array of card types with the PayPal expected card name format:
    
    public static $cardTypes = array(
        self::CardTypeVisa => "Visa", 
        self::CardTypeMasterCard => "MasterCard", 
        self::CardTypeDiscover => "Discover", 
        self::CardTypeAmericanExpress => "AmericanExpress"
    );
    
    const SandboxURI = "https://api-3t.sandbox.paypal.com/nvp";
    const LiveURI = "https://api-3t.paypal.com/nvp";

    protected $_environmentURIs = array(
        "sandbox" => self::SandboxURI,
        "live" => self::LiveURI
    );
    
    protected $_environment = "sandbox";
    
    protected $_version = "56";
    protected $_userName = "";
    protected $_password = "";
    protected $_signature = "";
    
    protected $_method = "";
    
    protected $_definition = array();
    protected $_requestData = array();

    protected $_errors = array();


    /**
     *
     * 
     */
    public function __construct($environment, $userName, $password, $signature)
    {
        if(!isset($this->_environmentURIs[strtolower($environment)]))
            throw new \Exception("Unknown environment: {$environment}");
        
        $this->_environment = $environment;
        
        $this->_userName = $userName;
        $this->_password = $password;
        $this->_signature = $signature; 
    
        $this->init();

    } // __construct()
    
    
    /**
     *
     *
     */
    protected function init()
    {

    } // init()


    /**
     *
     *
     */
    public function __set($var, $value)
    {            
        if(!isset($this->_definition[$var]))
            throw new \Exception("Unknown value {$var}");
        
        $this->_requestData[$this->_definition[$var]["field"]] = $value;
        
    } // __set()
    

    /**
     *
     *
     */
    protected function &findVariable($variable)
    {
        foreach($this->_definition as $key => &$element)
            if(strtolower(trim($key)) == strtolower(trim($variable)))
                return $element;

        return null;

    } // findVariable()


    /**
     *
     *
     */
    public function __call($method, $arguments)
    {

        if(substr($method, 0, 3) == "add")
        {
            $name = substr($method, 3);

            $var = $this->findVariable($name);

            if(is_null($var) || !isset($var["array"]) || $var["array"] !== true)
                throw new \Exception("Unknown multi value {$name}");

            $this->_requestData[$var["field"]][] = current($arguments);
        }

    } // __call()
    

    /**
     * 
     *
     */
    protected function validate()
    {
        // Loop each field in the definition:
        
        foreach($this->_definition as $externalKey => $field)
        {
            $fieldName = ucwords(str_replace("_", " ", $externalKey));
            $value = isset($this->_requestData[$field["field"]]) ? 
                $this->_requestData[$field["field"]] : null;
           
            // Required:
            
            if(isset($field["required"]) && $field["required"] === true)
            {
                if(empty($value) && strval($value) != "0" &&
                    (!isset($field["default"]) || empty($field["default"])))
                {
                    $this->_errors[] = "{$fieldName} is a required field.";
                }
            }
            
            // Type:
            
            if(!empty($value) && isset($field["type"]) && !empty($field["type"]))
            {
                switch(strtolower($field["type"]))
                {
                    case "carddate":
                        if(strlen($value) != 6 || strval($value) != strval(doubleval($value)))
                        {
                            $this->_errors[] = "{$fieldName} is not a valid card date.";
                        }
                        else
                        {
                            $month = substr($value, 0, 2);
                            $year = substr($value, 2);
                            
                            if($month < 1 || $month > 12)
                            {
                                $this->_errors[] = "{$fieldName} is not a valid card date.";                                    
                            }
                            else if($year < date("Y") || $year > date("Y", strtotime("+10 years")))
                            {
                                $this->_errors[] = "{$field} is not a valid card date.";
                            }
                            else
                            {
                                $expDate = date("Ymd", strtotime("{$month}/01/{$year}"));
                                $today = date("Ymd");
                                
                                if($expDate < $today)
                                    $this->_errors[] = "{$fieldName} is an expired card date.";
                            }
                        }
                    break;
                    
                    case "double":
                        if(strval($value) != strval(doubleval($value)))
                        {
                            $this->_errors[] = "{$fieldName} is not a floating point number.";
                        }
                    break;
                    
                    case "integer":
                        if(strval($value) != strval(intval($value)))
                        {
                            $this->_errors[] = "{$fieldName} is not an integer number.";
                        }                        
                    break;
                    
                    case "utcdate":
                    
                    break;
                    
                    default:
                        throw new \Exception("Unknown type validation requested: " . $field["type"]);
                }
            }
        }
        
        return count($this->_errors) == 0;
        
    } // validate()
    
    
    /**
     *
     */ 
    public function process()
    {
        if(!$this->validate())
            return false;
        
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $this->_environmentURIs[strtolower($this->_environment)]);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);

        $postString = "VERSION=" . urlencode($this->_version) . 
            "&PWD=" . urlencode($this->_password) . 
            "&USER=" . urlencode($this->_userName) . 
            "&SIGNATURE=" . urlencode($this->_signature) . 
            "&METHOD={$this->_method}";
            
        foreach($this->_definition as $key => $field)
        {
            $value = !empty($this->_requestData[$field["field"]]) ? 
                $this->_requestData[$field["field"]] : 
                (isset($field["default"]) && !empty($field["default"]) ? 
                    $field["default"] : "");
            
            if(is_null($value))
                continue;
       
            if(is_array($value))
                foreach($value as $index => $val)
                    $postString .= "&" . $field["field"] . $index . "=" . urlencode($val);
            else
                $postString .= "&" . $field["field"] . "=" . urlencode($value);
        }

        curl_setopt($curl, CURLOPT_POSTFIELDS, $postString);

        $response = curl_exec($curl);
        
        if(curl_errno($curl))
        {
            $this->_errors[] = "CURL ERROR (" . curl_errno($curl) . ") " . 
                curl_error($curl);
                
            return false;
        }
        else
        {
            curl_close($curl);
        }
        
        $responseData = explode("&", $response);
        
        $data = array();
        
        foreach($responseData as $key => &$value)
        {
            $values = explode("=", $value, 2);                                
            $data[$values[0]] = urldecode($values[1]);
        }
        
        if($data[ "ACK" ] == "Failure")
        {
            $i = 0; 
            
            while(true)
            {
                if(!isset($data["L_LONGMESSAGE{$i}"]))
                    break;
                
                $this->errors[] = $data["L_SEVERITYCODE{$i}"] . "(" . 
                    $data["L_ERRORCODE{$i}"] . ") " . $data["L_LONGMESSAGE{$i}"];
                
                $i++;
            }
        }
        
        return count($this->_errors) == 0 ? $data : false;
    
    } // request()
    
    
    /**
     *
     */ 
    protected function isError()
    {
        return count($this->_errors) > 0;
        
    } // isError()                        
    
    
    /**
     *
     */ 
    public function getErrors()
    {
        return $this->_errors;        
    
    } // getErrors()

} // Nvp()

?>
