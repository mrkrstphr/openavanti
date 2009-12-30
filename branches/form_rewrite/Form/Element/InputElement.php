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
 * @version         1.3.0-beta
 */


    /**
     * 
     *
     * @category    Forms
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/form
     */
    abstract InputElement extends FormElement
    {
        /**
         * Generate an input element for the form. Note that the supplied attributes are not 
         * validated to be valid attributes for the element. Each element provided is added to the 
         * XHTML tag.         
         * 
         * @param array An array of attributes for the HTML element
         * @param bool Controls whether or not to return the HTML, otherwise echo it, default false
         * @return void/string If bReturn is true, returns a string with the XHTML, otherwise void
         */
        public function render()
        {
            $html = "<input name=\"{$this->_name}\" id=\"{$this->_name}\" " .
                $this->generateAttributeString() . " value=\"{$this->_value}\" />";
            
            return $html;
            
        } // render()
        
        
        /*
        public static function Input( $aAttributes, $bReturn = false )
        {
            if( !isset( $aAttributes[ "type" ] ) )
            {
                return;
            }
            
            if( strtolower( $aAttributes[ "type" ] ) == "checkbox" ||
                strtolower( $aAttributes[ "type" ] ) == "radio" )
            {
                $sValue = self::TranslatePathForValue( $aAttributes[ "name" ] );
                
                if( isset( $aAttributes[ "value" ] ) && $aAttributes[ "value" ] == $sValue )
                {
                    $aAttributes[ "checked" ] = "checked";
                }
            }
            else if( strtolower( $aAttributes[ "type" ] ) != "password" )
            {
                $sValue = self::TranslatePathForValue( $aAttributes[ "name" ] );

                $aAttributes[ "value" ] = $sValue !== false ? $sValue : 
                    ( isset( $aAttributes[ "value" ] ) ? $aAttributes[ "value" ] : "" );
            }
        
            $sInput = "<input ";
            
            foreach( $aAttributes as $sKey => $sValue )
            {
                $sValue = htmlentities( $sValue );
                $sInput .= "{$sKey}=\"{$sValue}\" ";
            }
            
            $sInput .= " />";
            
            
            if( $bReturn )
            {
                return( $sInput );
            }
            else
            {
                echo $sInput;
            }
            
        } // Input()
        */
    } // InputElement()

?>
