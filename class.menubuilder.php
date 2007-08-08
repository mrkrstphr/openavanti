<?php

	class MenuBuilder
	{
		protected $sHTML;
		
		public function __construct()
		{
		
		}
		
		
		public function Render( $sMenuFile )
		{
			$this->sHTML = "";
			
			if( file_exists( $sMenuFile ) )
			{
			
				$oMenu = simplexml_load_file( $sMenuFile );
				
				if( isset( $oMenu->Element ) )
				{
					foreach( $oMenu->Element as $oElement )
					{
						$sTitle = (string)$oElement[ "title" ];
						$sURI = (string)$oElement[ "uri" ];
						
						$this->sHTML .= "\t<li><a href=\"{$sURI}\">{$sTitle}</a></li>\n";
					}
				}
			
				$this->sHTML = "<ul>{$this->sHTML}</ul>";
			
			}
			
			return( $this->sHTML );
		
		} // Render()
	
	}

?>
