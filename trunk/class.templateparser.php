<?php


	/////////////////////////////////////////////////////////////////////////////////////////////////	
	class TemplateParser
	{
		public $aContentAreas = array();
		
		private $sLastContentArea = "";
		private $sLastCustomField = "";
		
		private $aErrors = array();
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function __construct()
		{
		
		
		} // __construct()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		public function Parse( $sTemplateString )
		{
			// Load the XML string into a SimpleXML Object:
			$oTemplate = new SimpleXMLElement( $sTemplateString );

			// Register the PHPTal namespace so we can search it with XPath:
			$oTemplate->registerXPathNamespace( "tal", "http://phptal.motion-twin.com" );

			// Run a search for any nodes in the tal namespace:
			$aResults = $oTemplate->xpath( "//tal:*" );

			// Loop each result:
			foreach( $aResults as $oResult )
			{
				// Get the attributes that belong to the tal namespace (via URL):
				$aAttributes = $oResult->attributes( "http://phptal.motion-twin.com" );
					
				// Used independently:
				$sDefines = isset( $aAttributes[ "define" ] ) ? 
					(string)$aAttributes[ "define" ] : "";
				
				// Loop each tal attribute:
				foreach( $aAttributes as $sKey => $oValue )
				{
					// Convert the SimpleXML object to a string:
					$sValue = (string)$oValue;
					
					// We currently use two attributes, content and attributes:
					if( strtolower( $sKey ) == "content" )
					{
						// Parse the content area:
						$this->ParseContentArea( $sValue, $sDefines );
					}
					else if( strtolower( $sKey ) == "attributes" )
					{
						// With attributes, we have extra data not contained in content,
						// which is the attribute to replace:
						//
						// 	src image/content
						//
						// Explode on a space and only use the path:
						
						$aParts = explode( " ", $sValue );
						
						// Verify that we have two pieces, otherwise we have dirty XML:
						if( count( $aParts ) != 2 )
						{
						
						}
						
						// Parse the path:
						$this->ParseContentArea( $aParts[ 1 ], $sDefines );
					}
					
				} // foreach( attributes )
			
			} // foreach( xpath result )
			
			
			// Leave it up to the application to decide what to do with this information.
		
			return( count( $this->aErrors ) == 0 );
		
		} // Parse()
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////
		private function ParseContentArea( $sPath, $sDefines )
		{
			// Explode the path into pieces:
			$aPath = explode( "/", $sPath );
			
			// If we don't have at least two elements, the XML is dirty:
			if( count( $aPath ) < 2 )
			{
				
			}
			
			// Get the name of the content area:
			$sContentArea = $aPath[ 0 ];
			
			// If we have not yet found this area, add it to our array:
			if( !isset( $this->aContentAreas[ $sContentArea ] ) )
			{
				$this->aContentAreas[ $sContentArea ] = array(
					"custom" => array()
				);
			}
			
			// If this area contains a custom field, add it to the area's custom field array:
			if( strtolower( $aPath[ 1 ] == "custom" ) )
			{
				// Each custom field must have a set of defines that specify information about it
				
				// If no defines, error out.
				if( empty( $sDefines ) )
				{
				
				}
				
				$aDefines = explode( ";", $sDefines );
				
				if( count( $sDefines ) != 2 )
				{
				
				}
								
				
				$this->aContentAreas[ $sContentArea ][ "custom" ][ $aPath[ 2 ] ] = array();
				
				$aContentArea = array();
				
				foreach( $aDefines as $sDefine )
				{
					$aDefine = explode( " ", trim( $sDefine ) );
										
					if( count( $aDefine ) != 2 )
					{
						continue; // set error;
					}
					
					$sName = $aDefine[ 0 ];
					$aValue = explode( ":", $aDefine[ 1 ] );
					
					if( count( $aValue ) != 2 )
					{
						continue; // set error;
					}
					
					$sValue = $aValue[ 1 ];
					
					$aContentArea[ $sName ] = $sValue;
				}
				
				$this->aContentAreas[ $sContentArea ][ "custom" ][ $aPath[ 2 ] ] = $aContentArea;
			}			
			
		} // ParseContentArea()
	
	
	}; // TemplateParser()

?>
