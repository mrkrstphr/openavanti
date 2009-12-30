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
    class LabelElement extends FormElement
    {
        /**
         * Generate a label for the form. Note that the supplied attributes are not validated to be
         * valid attributes for the element. Each element provided is added to the XHTML tag. The
         * "label" element of aAttributes specifies the text of the label.            
         * 
         * @param array An array of attributes for the HTML element
         * @param bool Controls whether or not to return the HTML, otherwise echo it, default false
         * @return void/string If bReturn is true, returns a string with the XHTML, otherwise void
         */
        /*public static function Label( $aAttributes, $bReturn = false )
        {
            if( !isset( $aAttributes[ "label" ] ) )
            {
                return;
            }
            
            $sLabel = $aAttributes[ "label" ];
            unset( $aAttributes[ "label" ] );
                
                
            if( class_exists( "Validation" ) && isset( $aAttributes[ "for" ] ) && 
                Validation::FieldHasErrors( $aAttributes[ "for" ] ) )
            {
                $aAttributes[ "class" ] = isset( $aAttributes[ "class" ] ) ? 
                    $aAttributes[ "class" ] . " error" : "error";   
            }
            
            $sInput = "<label ";
            
            foreach( $aAttributes as $sKey => $sValue )
            {
                $sInput .= "{$sKey}=\"{$sValue}\" ";
            }
            
            $sInput .= ">{$sLabel}</label>";
            
            
            if( $bReturn )
            {
                return( $sInput );
            }
            else
            {
                echo $sInput;
            }
            
        } // Label()
        */
    } // LabelElement()

?>
