<?php

	class ClassFunctions
	{
	
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function Create( $sClass, $sParent = null, $aConstructorArgs = array() )
		{
			$sConstructorArgs = implode( ", ", $aConstructorArgs );
		
			$sNewClass = "class {$sClass} " . 
				( !empty( $sParent ) ? " extends {$sParent} " : "" ) . "
			{
				public function __construct() 
				{
					\$aArguments = func_get_args();
					
					array_unshift( \$aArguments, {$sConstructorArgs} );
					
					call_user_func_array( array( 'parent', '__construct' ), \$aArguments );
				}
			};";
			
			//echo "<pre>" . htmlspecialchars( $sNewClass ) . "</pre>";
			
			
			eval( $sNewClass );
			
			return( $sClass );
			
		} // Create()
	
	};

?>
