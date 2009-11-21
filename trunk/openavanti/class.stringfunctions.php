<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.2.0-beta
 *
 */
 
    /**
     * A library for manipulating strings
     *
     * @category    String
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/stringfunctions
     */
    class StringFunctions
    {
        protected static $_dictionary = array(
            "person" => "people", "deer" => "deer", "beer" => "beer", 
            "goose" => "geese", "mouse" => "mice", "content" => "content"
        );
        
        /**
         *
         *
         */                     
        private function __construct()
        {
            // this class cannot be instantiated
            
        } // __construct()


        /**
         * Attempts to turn a supplied string, preferably an English, singular word, into the
         * plural version of the string.
         *
         * @argument string the singular word to attempt to make plural
         * @returns string the result of attempting to make the word plural
         */
        public static function toSingular($inputString)
        {
            $dictionary = array_reverse(self::$_dictionary);
            
            if(isset($dictionary[strtolower($inputString)]))
            {
                return $dictionary[strtolower($inputString)];
            }
        
            if(substr($inputString, strlen($inputString) - 3, 3) == "ies")
            {
                $inputString = substr($inputString, 0, strlen($inputString) - 3) . "y";
            }
            else if(substr($inputString, strlen($inputString) - 2, 2) == "es")
            {
                $inputString = substr($inputString, 0, strlen($inputString) - 2);
            }
            else if(substr($inputString, strlen($inputString) - 1, 1) == "s")
            {
                $inputString = substr($inputString, 0, strlen($inputString) - 1);
            }
            
            return $inputString;
        
        } // toSingular()
        
        
        /**
         * Attempts to turn a supplied string, preferably an English, plural word, into the
         * singular version of the string.
         *
         * @argument string the plural word to attempt to make singular
         * @returns string the result of attempting to make the word singular
         */
        public static function toPlural($inputString)
        {       
            if(isset(self::$_dictionary[strtolower($inputString)]))
            {
                return self::$_dictionary[strtolower($inputString)];
            }
        
            if(substr($inputString, strlen($inputString) - 1, 1) == "y")
            {
                $inputString = substr($inputString, 0, strlen($inputString) - 1 ) . "ies";
            }
            elseif(substr($inputString, strlen($inputString) - 1, 1) == "x")
            {
                $inputString .= "es";
            }
            else if(substr($inputString, strlen( $inputString) - 1, 1) != "s")
            {
                $inputString .= "s";
            }
            
            return $inputString;
        
        } // toPlural()
        
        
        /**
         * Allows for adding custom words to the single/plural dictionary used by the toSingular()
         * and toPlural() methods. Either two strings can be supplied as arguments, the singular
         * and plural forms of the words, respectively, or one array can be given that contains
         * an associative array of rules in the form of singular => plural.
         *
         * @argument string|array Either the singular form of the word, or an array of 
         *      single => plural
         * @argument string Optional; Either the plural form of the word or null if an array was
         *      passed for the first argument
         * @returns array A copy of the dictionary
         */
        public static function addToDictionary($single, $plural = null)
        {
            if(is_array($single))
            {
                // We could simply add or merge this array with the dictionary, but let's loop
                // each element and make sure both the key and value are strings to try to prevent
                // bad data:
                
                foreach($single as $key => $value)
                {
                    if(is_string($key) && is_string($value))
                    {
                        self::$_dictionary[strtolower(strval($key))] = strtolower(strval($value));  
                    }
                }
            }
            else if(!empty($plural) && is_string($plural))
            {
                self::$_dictionary[strtolower(strval($single))] = strtolower(strval($plural));
            }
            
            return self::$_dictionary;
            
        } // addToDictionary()
        
        
        /**
         * Allows the retrieval of the full dictionary used by toSingular() and toPlural().
         *
         * @returns array A copy of the dictionary
         */
        public function getDictionary()
        {
            return self::$_dictionary;
            
        } // getDictionary()
        
        
        /**
         * Returns a substring of the supplied string, starting after the last occurrence   of the
         * supplied delimiter.        
         *
         * @argument string The string we're generating a substring from
         * @argument string The delimiter that we're searching for
         * @returns string The generated substring
         */
        public static function afterLastOccurrenceOf($inputString, $deliminator)
        {
            return(substr($inputString, strrpos($inputString, $deliminator) + 1));
        
        } // afterLastOccurrenceOf()
    
    } // StringFunctions()

?>
