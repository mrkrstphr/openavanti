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
 *
 */
 
    /**
     * Based on properties added, this class is responsible for building a list of elements,
     * and then generating a JSON string from those elements. This is more powerful than
     * json_encode() in that it allows for adding arrays and objects as member variables
     * to the JavaScript object.             
     *
     * @category    Database
     * @author      Kristopher Wilson
     * @package     openavanti
     * @link        http://www.openavanti.com/documentation/docs/1.0.3/JSONObject
     */
    class JSONObject
    {
        /**
         * An array of attributes for the JSON string
         */
        private $aAttributes = array();
        
        
        /**
         * Constructor. Currently does nothing.
         */                         
        public function __construct()
        {
        
        } // __construct()
    
    
        /**
         * Adds the specified key/value pair to our list of attributes in the JavaScript object.          
         * key/value will be converted to { 'key': 'value } in JSON. The value parameter can be
         * either a string, number (integer or float), array, or another JSONObject object. If
         * an array is passed, it may also contain any of the previously listed types. Passed arrays
         * and JSONObject objects will be recursively parsed.                        
         *       
         * @param string $sKey The key for this attribute of the JavaScript object
         * @param mixed $xValue The value for this attribute of the JavaScript object, either a
         *      string, number or another JSONObject object may be passed. 
         */
        public function AddAttribute( $sKey, $xValue )
        {
            $this->aAttributes[ $sKey ] = $xValue;
            
        } // AddAttribute()
    
    
        /**
         * Protected method to return the elements in this JSONObject            
         *       
         * @return array The array of elements stored in this object
         */
        protected function GetAttributes()
        {
            return( $this->aAttributes );
            
        } // GetAttributes()
        
        
        /**
         * Protected method for looping the supplied attributes and turning them into a JSON string.
         * This method is called recursively when a value of array or JSONObject is found in the
         * provided attributes array                     
         * 
         * @param array $aAttributes The array of elements to parse into JSON
         * @param string $sStartChar Optional; The starting character of this JSON attribute,
         *      either { for an object or [ for an array. Default: {
         * @param string $sEndChar The ending character of this JSON attribute, either } for an
         *      object or ] for an array. Default: }
         * @return string The JSON string for the provided elements
         */
        protected static function ConvertJSONAttributes( $aAttributes, $sStartChar = '{', $sEndChar = '}' )
        {
            $sJSONAttributes = "";
            
            foreach( $aAttributes as $sKey => $xValue )
            {
                $sJSONAttributes .= !empty( $sJSONAttributes ) ? ",\n" : "";
                
                
                $sJSONAttributes .= !is_numeric( $sKey ) ? 
                    "\t'{$sKey}': " : "";
                    
                if( is_string( $xValue ) ) 
                {
                    $sJSONAttributes .= "'" . addslashes( $xValue ) . "'";
                }
                else if( is_int( $xValue ) || is_float( $xValue ) )
                {
                    $sJSONAttributes .= $xValue;
                }
                else if( is_array( $xValue ) )
                {
                    $sJSONAttributes .= JSONObject::ConvertJSONAttributes( $xValue, '[', ']' ); 
                }
                else if( is_object( $xValue ) && get_class( $xValue ) == "JSONObject" )
                {
                    $sJSONAttributes .=     JSONObject::ConvertJSONAttributes( $xValue->GetAttributes() );  
                }   
                else if( is_null( $xValue ) )
                {
                    $sJSONAttributes .= "''";
                }
            }
            
            $sJSON = !empty( $sJSONAttributes ) ? 
                "{$sStartChar}\n{$sJSONAttributes}\n{$sEndChar}" : "{}";
            
        
            return( $sJSON );
            
        } // ConvertJSONAttributes()
        
        
        /**
         * Converts the object into a string by parsing the attributes array. This method
         * calls the protected ConvertJSONAttributes method and returns its output                       
         * 
         * @return string The JSON string for the attributes stored in this class
         */
        public function __toString()
        {
            return( JSONObject::ConvertJSONAttributes( $this->aAttributes ) );
        
        } // __toString()
    
    } // JSONObject()

?>
