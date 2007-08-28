<?php

	////////////////////////////////////////////////////////////////////////////////////////////////
	class Templating
	{
	
		////////////////////////////////////////////////////////////////////////////////////////////
		public static function RenderTemplateFile( $sTemplateFile, $aTemplateVars )
		{
		
			if( ( $sTemplateFile = FileFunctions::FileExistsInPath( $sTemplateFile ) ) !== false )
			{
				return( null );
			}
			
			$sTemplate = file_get_contents( $sTemplateFile );
			
			self::RenderTemplate( $sTemplate, $aTemplateVars );
		
		} // RenderTemplateFile()
	
	
		public static function RenderTemplate( $sTemplate, $aTemplateVars )
		{
		
		
		} // RenderTemplate()
	
	};

?>
