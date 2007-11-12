<?php

	/////////////////////////////////////////////////////////////////////////////////////////////////
	class Validation
	{
		private static $errors = array();
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		private function __construct()
		{
		
		} // __construct()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public static function HasErrors()
		{		
			return( count( self::$errors ) > 0 );
		
		} // HasErrors()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public static function GetErrors()
		{
			return( self::$errors );
			
		} // GetErrors()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public static function Clear()
		{
			self::$errors = array();
		
		} // Clear()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function ValidateInteger( $value, $error )
		{
			if( !filter_var( $value, FILTER_VALIDATE_INT ) )
			{
				self::$errors[] = $error;
			}
		
		} // ValidateInteger()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public static function ValidateLength( $sName, $sValue, $iMin = null, $iMax = null )
		{
		
			if( ( !is_null( $iMin ) && strlen( $sValue ) < $iMin ) || 
				( !is_null( $iMax ) && strlen( $sValue > $iMax ) ) )
			{
				self::$errors[ $sName ] = ucwords( str_replace( "_", " ", $sName ) ) . 
					" must be ";
					
				if( !is_null( $iMin ) )
				{
					self::$errors[ $sName ] .= " more than {$iMin} characters ";
				}
				
				self::$errors[ $sName ] .= !is_null( $iMin ) && !is_null( $iMax ) ? 
					" and " : "";
										
				if( !is_null( $iMax ) )
				{
					self::$errors[ $sName ] .= " less than {$iMax} characters ";
				}
				
				self::$errors[ $sName ] .= "in length.";
			}
		
		} // ValidateLength()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public static function ValidateFilePresent( $sName, $sValue )
		{
		
			if( empty( $sValue ) )
			{
				self::$errors[ $sName ] = ucwords( str_replace( "_", " ", $sName ) ) . " is required.";
			}
		
		} // ValidateFilePresent()
				
		
	}; // Validation()

?>
