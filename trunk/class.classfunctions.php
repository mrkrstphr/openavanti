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
					parent::__construct( {$sConstructorArgs} );
				}
			};";
			
			
			eval( $sNewClass );
			
		} // Create()
	
	};

?>
