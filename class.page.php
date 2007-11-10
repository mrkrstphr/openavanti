<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @dependencies 	CRUD, Database, StringFunctions, FileFunctions, SimpleXML, PHPTal
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link				http://www.openavanti.com
 * @version			0.05
 *
 */
 
	/**
	 * Page class generates HTML output for a given page, using the XML templates
	 * @category	Templating
	 * @author		Kristopher Wilson
	 * @link			http://www.openavanti.com/docs/templating
	 */
	class Page
	{

		///////////////////////////////////////////////////////////////////////////////////////////
      private function __construct()
      {

      } // __construc()


      ///////////////////////////////////////////////////////////////////////////////////////////
      public static function Parse( $sPageTag, $iLanguageID )
      {
			if( substr( $sPageTag, strlen( $sPageTag ) - 1, 1 ) == "/" )
			{
				$sPageTag = substr( $sPageTag, 0, strlen( $sPageTag ) - 1 );
			}
			
			if( substr( $sPageTag, 0, 1 ) == "/" )
			{
				$sPageTag = substr( $sPageTag, 1 );
			}
			
			// Load the page:
			$oPage = new crud( "pages" );
			$oPage->Find( null, array(
				"where" => "url_alias = '{$sPageTag}'"
			) );
			
			// Throw an exception if the page does not exist:
			if( $oPage->Empty() )
			{
				throw new Exception( "Page Not Found: {$sPageTag}" );
			}
			
			// Find the template file:
			$sTemplateFile = $oPage->template->file_name;
			$sTemplateFile = FileFunctions::FileExistsInPath( $sTemplateFile );
			
			if( !$sTemplateFile )
			{
				return( "Failed to load template: {$oPage->template->file_name}" );
			}
			
			// Load the template:        
			$oTemplate = new PHPTAL( $sTemplateFile );
			$oTemplate->title = $oPage->title;
			
			
			// Loop each page content element:
			while( !$oPage->page_content->Empty() )
			{
				// Load the page content:
				$oPageContent = new StdClass();
				
				$oContentLanguage = new crud( "content_languages" );
				$oContentLanguage->Find( array( 
					"content_id" => $oPage->page_content->content_id,
					"language_id" => $iLanguageID
				) );
				
				// get the content and content language record:
				
				$oPageContent->title = $oContentLanguage->title; //$oPage->page_content->content->title;
				$oPageContent->body = $oContentLanguage->body; //$oPage->page_content->content->body;
				
				$oTemplate->set( $oPage->page_content->template_content_area->tag, $oPageContent );
				
				
				$oPage->page_content->Next();
			}
			
			
			$sOutput = $oTemplate->execute();
			
			$oXML = new SimpleXMLElement( $sOutput );
			
			/*$oScript = $oXML->head->addChild( "script", " " );
			$oScript[ "type" ] = "text/javascript";
			$oScript[ "src" ] = "/js/cms.js";
			
			$oScript = $oXML->head->addChild( "script", " " );
			$oScript[ "type" ] = "text/javascript";
			$oScript[ "src" ] = "/js/tiny_mce/tiny_mce.js";
			
			$oContentAreas = $oXML->xpath( "//div[@class='cms-area']" );
			
			foreach( $oContentAreas as $oContentArea )
			{
			    self::CreateCMSLinks( $oContentArea, $sPageTag, (string)$oContentArea[ "id" ] );
			}*/
			
			// Return the generated page:
			return( $oXML->asXML() );

      } // ParseTemplate()


      ///////////////////////////////////////////////////////////////////////////////////////////
      protected function CreateCMSLinks( $oParent, $sPageTag, $sContentArea )
      {
			/*
			$oLinks = $oParent->addChild( "div" );
			$oLinks[ "class" ] = "cms-action-links";
			$oLinks[ "id" ] = $sContentArea . "-cms-links";
			
			$oEditLink = $oLinks->addChild( "a" );
			$oEditLink[ "href" ] = "#";
			$oEditLink[ "onclick" ] = "editContent( '{$sPageTag}', '{$sContentArea}' );"; 
			
			$oEdit = $oEditLink->addChild( "img" );
			$oEdit[ "alt" ] = "Edit";
			$oEdit[ "title" ] = "Edit Content";
			$oEdit[ "src" ] = "/images/edit.png";
			*/
				
      } // CreateCMSLinks()

    }; // Page()

?>
