<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	FileInfo
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link				http://www.openavanti.com
 * @version			0.6.4-alpha
 *
 */
 
	/**
	 * A library of form field generation helpers to automate data preservation
	 *
	 * @category	Forms
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/form
	 */
	class Form 
	{
		public static $aFields = array();
		
		/**
		 * Loads the specified array into the classes aFields array. These values are later
		 * used by the field generation helpers for setting the value of the form field.		 
		 * 
		 * @argument array An array of keys and values to load into the forms data array
		 * @returns void
		 */
		public static function LoadArray( $aArray )
		{
			self::$aFields += $aArray;
		
		} // LoadArray()

		/**
		 * Loads the specified array into the classes aFields array. These values are later
		 * used by the field generation helpers for setting the value of the form field.		 
		 * 
		 * @argument array An array of keys and values to load into the forms data array
		 * @returns void
		 */
		public static function LoadObject( $oObject )
		{
			foreach( $oObject as $sKey => $sValue )
			{
				if( !is_object( $sValue ) || !is_array( $sValue ) )
				{
					self::$aFields[ $sKey ] = $sValue;
				}
			}
		
		} // LoadArray()		
		
		/**
		 * Generate a label for the form. Note that the supplied attributes are not validated to be
		 * valid attributes for the element. Each element provided is added to the XHTML tag. The
		 * "label" element of aAttributes specifies the text of the label.		 	  
		 * 
		 * @argument array An array of attributes for the HTML element
		 * @argument bool Controls whether or not to return the HTML, otherwise echo it, default false
		 * @returns void/string If bReturn is true, returns a string with the XHTML, otherwise void
		 */
		public static function Label( $aAttributes, $bReturn = false )
		{
			$sLabel = "Element";
			
			if( isset( $aAttributes[ "label" ] ) )
			{
				$sLabel = $aAttributes[ "label" ];
			
				unset( $aAttributes[ "label" ] );
			}	
			
			$sInput = "<label ";
			
			foreach( $aAttributes as $sKey => $sValue )
			{
				$sInput .= "{$sKey}=\"{$sValue}\" ";
			}
			
			$sInput .= ">{$sLabel}:</label>";
			
			
			if( $bReturn )
			{
				return( $sInput );
			}
			else
			{
				echo $sInput;
			}
			
		} // Label()
		
		
		/**
		 * Generate an input element for the form. Note that the supplied attributes are not 
		 * validated to be valid attributes for the element. Each element provided is added to the 
		 * XHTML tag.		  
		 * 
		 * @argument array An array of attributes for the HTML element
		 * @argument bool Controls whether or not to return the HTML, otherwise echo it, default false
		 * @returns void/string If bReturn is true, returns a string with the XHTML, otherwise void
		 */
		public static function Input( $aAttributes, $bReturn = false )
		{
			if( isset( self::$aFields[ $aAttributes[ "name" ] ] ) )
			{
				$aAttributes[ "value" ] = self::$aFields[ $aAttributes[ "name" ] ];
			}
		
			$sInput = "<input ";
			
			foreach( $aAttributes as $sKey => $sValue )
			{
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
		
		
		/**
		 * Generate a select element for the form. Note that the supplied attributes are not 
		 * validated to be valid attributes for the element. Each element provided is added to the 
		 * XHTML tag.
		 * 
		 * The options are specified by aAttributes[ options ] as an array of key => values to
		 * display in the select		 
		 *		 		 
		 * The default (selected) attribute is controlled by aAttributes[ default ], which should
		 * match a valid key in aAttributes[ options ]		 		 		   
		 * 
		 * @argument array An array of attributes for the HTML element
		 * @argument bool Controls whether or not to return the HTML, otherwise echo it, default false
		 * @returns void/string If bReturn is true, returns a string with the XHTML, otherwise void
		 */
		public static function Select( $aAttributes, $bReturn = false )
		{
			$sDefault = "";
			
			if( isset( self::$aFields[ $aAttributes[ "name" ] ] ) )
			{
				$sDefault = self::$aFields[ $aAttributes[ "name" ] ];
			}
			else if( isset( $aAttributes[ "default" ] ) )
			{
				$sDefault = $aAttributes[ "default" ];
			}
		
			$sSelect = "<select name=\"" . $aAttributes[ "name" ] . "\">\n";
			
			foreach( $aAttributes[ "options" ] as $sKey => $sValue )
			{
				$sSelected = $sKey == $sDefault ? 
					" selected=\"selected\" " : "";
					
				$sSelect .= "\t<option value=\"{$sKey}\"{$sSelected}>{$sValue}</option>\n";
			}
			
			$sSelect .= "\n</select>\n";
			
			
			if( $bReturn )
			{
				return( $sSelect );
			}
			else
			{
				echo $sSelect;
			}
		
		} // Select()
		
		
		/**
		 * Generate a textarea element for the form. Note that the supplied attributes are not 
		 * validated to be valid attributes for the element. Each element provided is added to the 
		 * XHTML tag.		  
		 * 
		 * @argument array An array of attributes for the HTML element
		 * @argument bool Controls whether or not to return the HTML, otherwise echo it, default false
		 * @returns void/string If bReturn is true, returns a string with the XHTML, otherwise void
		 */
		public static function TextArea( $aAttributes, $bReturn = false )
		{		
			$sInput = "<textarea ";
			
			foreach( $aAttributes as $sKey => $sValue )
			{
				$sInput .= "{$sKey}=\"{$sValue}\" ";
			}
			
			$sInput .= ">";
			
			if( isset( self::$aFields[ $aAttributes[ "name" ] ] ) )
			{
				$sInput .= self::$aFields[ $aAttributes[ "name" ] ];
			}
			
			$sInput .= "</textarea>";
			
			
			if( $bReturn )
			{
				return( $sInput );
			}
			else
			{
				echo $sInput;
			}
			
		} // TextArea()

	}; // Form()

?>
