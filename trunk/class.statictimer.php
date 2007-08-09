<?php

	////////////////////////////////////////////////////////////////////////////////////////////////
	class StaticTimer
	{
		private static $iStart = 0;
		private static $iEnd = 0;
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function Start()
		{
			self::Update( self::$iStart );
			
		} // Start()
		
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function Stop()
		{
			self::Update( self::$iEnd );
			
			return( self::$iEnd - self::$iStart );
			
		} // Stop()
		
		
		////////////////////////////////////////////////////////////////////////////////////////////
		protected static function Update( &$iVar )
		{
			$sMicrotime = microtime();
						
			$aParts = explode( " ", $sMicrotime );
			
			$iVar = ( (float)$aParts[ 0 ] + (float)$aParts[ 1 ] );
			
		} // Update()
	
	}; // StaticTimer()

?>
