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
 * @version			0.05a
 *
 */
 
	/**
	 * A library for manipulating strings
	 *
	 * @category	String
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/stringfunctions
	 */
	class StringFunctions
	{

        /**
         * Attempts to turn a supplied string, preferably an English, singular word, into the
         * plural version of the string.
         *
         * @argument string the singular word to attempt to make plural
         * @returns string the result of attempting to make the word plural
         */
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
		
		
        /**
         * Attempts to turn a supplied string, preferably an English, plural word, into the
         * singular version of the string.
         *
         * @argument string the plural word to attempt to make singular
         * @returns string the result of attempting to make the word singular
         */
		public static function ToPlural( $sString )
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
		
		} // ToSingular())
	
	}; // SringFunctions()

?>
