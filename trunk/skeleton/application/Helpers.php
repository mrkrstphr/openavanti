<?php
/***************************************************************************************************
 * dotPortal
 *
 * Project Management On Crack
 *
 * @author			Kristopher Wilson
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @link			http://dotportal.kristopherwilson.com
 * @version			0.03
 *
 */


	/**
	 * This class provides an interface to several helpers. Helpers are reusable pieces of code that
	 * generate output. These helpers are passed a set of variables, which are in turn used by the
	 * loaded helper view file.	 	 	 
	 *
	 * @author		Kristopher Wilson
	 * @modified	2008-05-15
	 */ 
	class Helpers
	{
		// Icon constants; used by one or more helper views:
		
		const IconAdd = "/images/add.png";
		const IconSearch = "/images/zoom.png";
		const IconEdit = "/images/page_white_edit.png";
		const IconRotate = "/images/arrow_rotate_clockwise.png";
		const IconView = "/images/zoom.png";
		const IconDate = "/images/date.png";
		
		
		/**
		 * Generates the standard page header, which contains a page title and an action icon and
		 * action link.
		 * 
		 * @argument string The title of the page
		 * @argument string The caption of the action link and title of the action image
		 * @argument string The URL of the action link and icon
		 * @argument string The location of the action icon's image
		 */		 		 		 		 		 		 		 		
		public static function page_header( $sTitle, $sCaption = null, $sLink = null, $sIcon = null )
		{			
			require( "helpers/page_header.php" );
			
		} // page_header()
		
		
		/**
		 * Generates a linked icon from the supplied caption, link and icon name.
		 * 
		 * @argument string The caption of the action link and title of the action icon
		 * @argument string The URL of the action link and icon
		 * @argument string The location of the action icon's image
		 */		 		 		
		public static function action_icon( $sCaption, $sLink, $sIcon )
		{			
			require( "helpers/action_icon.php" );
		
		} // action_icon()

	} // Helpers()

?>
