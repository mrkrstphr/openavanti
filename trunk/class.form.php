<?php

	class Form 
	{
		public static $aFields = array();
		
		
		public static function LoadArray( $aArray )
		{
			foreach( $aArray as $sKey => $sValue )
			{
				self::$aFields[ $sKey ] = $sValue;
			}
		
		} // LoadArray()
		
		
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
			
			$sInput .= ">";
			
			$sInput .= "{$sLabel}:";
			
			$sInput .= "</label>";
			
			
			if( $bReturn )
			{
				return( $sInput );
			}
			else
			{
				echo $sInput;
			}
			
		} // Label()
		
		
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
