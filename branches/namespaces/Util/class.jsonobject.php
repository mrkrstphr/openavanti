<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    
 * @copyright       Copyright (c) 2008, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.2.0-beta
 *
 */
 
    namespace OpenAvanti\Util;
    
    /**
     * Based on properties added, this class is responsible for building a list of elements,
     * and then generating a JSON string from those elements. This is more powerful than
     * json_encode() in that it allows for adding arrays and objects as member variables
     * to the JavaScript object.             
     *
     * @category    Database
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/jsonobject
     */
    class JSONObject
    {
        private $_attributes = array();
    
    
        /**
         * Adds the specified key/value pair to our list of attributes in the JavaScript object.          
         * key/value will be converted to { 'key': 'value } in JSON. The value parameter can be
         * either a string, number (integer or float), array, or another JSONObject object. If
         * an array is passed, it may also contain any of the previously listed types. Passed arrays
         * and JSONObject objects will be recursively parsed.                        
         *       
         * @param string The key for this attribute of the JavaScript object
         * @param mixed The value for this attribute of the JavaScript object, either a string, 
         *       number or another JSONObject object may be passed.      
         * @return void
         */
        public function addAttribute($key, $value)
        {
            $this->_attributes[$key] = $value;
            
        } // addAttribute()
    
    
        /**
         * Protected method to return the elements in this JSONObject            
         *       
         * @return array The array of elements stored in this object
         */
        protected function getAttributes()
        {
            return $this->_attributes;
            
        } // getAttributes()
        
        
        /**
         * Protected method for looping the supplied attributes and turning them into a JSON string.
         * This method is called recursively when a value of array or JSONObject is found in the
         * provided attributes array                     
         * 
         * @param array The array of elements to parse into JSON
         * @param string The starting character of this JSON attribute, either { for an object 
         *       or [ for an array               
         * @param string The ending character of this JSON attribute, either } for an object 
         *       or ] for an array  
         * @return string The JSON string for the provided elements
         */
        protected static function convertJSONAttributes($attributes, $startChar = '{', $endChar = '}')
        {
            $jsonAttributes = "";
            
            foreach($attributes as $key => $value)
            {
                $jsonAttributes .= !empty($jsonAttributes) ? ",\n" : "";
                
                
                $jsonAttributes .= !is_numeric($key) ? "\t'{$key}': " : "";
                    
                if(is_string($value)) 
                {
                    $jsonAttributes .= "'" . addslashes($value) . "'";
                }
                else if(is_int($value) || is_float($value))
                {
                    $jsonAttributes .= $value;
                }
                else if(is_array($value))
                {
                    $jsonAttributes .= JSONObject::convertJSONAttributes($value, '[', ']'); 
                }
                else if(is_object($value) && get_class($value) == "JSONObject")
                {
                    $jsonAttributes .= JSONObject::convertJSONAttributes($value->getAttributes());  
                }   
                else if(is_null($value))
                {
                    $jsonAttributes .= "''";
                }
            }
            
            $sJSON = !empty($jsonAttributes) ? 
                "{$startChar}\n{$jsonAttributes}\n{$endChar}" : "{}";
            
        
            return $sJSON;
            
        } // convertJSONAttributes()
        
        
        /**
         * Converts the object into a string by parsing the attributes array. This method
         * calls the protected ConvertJSONAttributes method and returns its output                       
         * 
         * @return string The JSON string for the attributes stored in this class
         */
        public function __toString()
        {
            return JSONObject::convertJSONAttributes($this->_attributes);
        
        } // __toString()
    
    } // JSONObject()

?>