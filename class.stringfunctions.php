<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link				http://www.openavanti.com
 * @version			0.05
 *
 */
 
	/**
	 * A library for manipulating strings
	 *
	 * @category	String
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/strings
	 */
	class StringFunctions
	{
	
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function ToSingular( $sString )
		{
		
			if( strtolower( $sString ) == "people" )
			{
				return( "person" );
			}
		
			if( substr( $sString, strlen( $sString ) - 3, 3 ) == "ies" )
			{
				$sString = substr( $sString, 0, strlen( $sString ) - 3 ) . "y";
			}
			else if( substr( $sString, strlen( $sString ) - 2, 2 ) == "es" )
			{
				$sString = substr( $sString, 0, strlen( $sString ) - 2 );
			}
			else if( substr( $sString, strlen( $sString ) - 1, 1 ) == "s" )
			{
				$sString = substr( $sString, 0, strlen( $sString ) - 1 );
			}
			
			return( $sString );
		
		} // ToSingular()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function ToPlural( $sString )
		//
		// Description:
		//		Attempts to turn a word into a plural form from a singular form.
		//		WARNING: this method makes horrible assumptions. Consider overloading/extending
		//		this method if a word does not follow the following rules:
		//
		//		{*}y => {*}ies
		//		{*} => {*}s
		//
		{
				
			if( strtolower( $sString ) == "person" )
			{
				return( "people" );
			}
		
			if( substr( $sString, strlen( $sString ) - 1, 1 ) == "y" )
			{
				$sString = substr( $sString, 0, strlen( $sString ) - 1 ) . "ies";
			}
			else if( substr( $sString, strlen( $sString ) - 1, 1 ) != "s" )
			{
				$sString .= "s";
			}
			
			return( $sString );
		
		} // Pluralize()
	
	} // SringFunctions()

?>
