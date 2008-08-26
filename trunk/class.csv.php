<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author			Kristopher Wilson
 * @copyright		Copyright (c) 2008, Kristopher Wilson
 * @license			http://www.openavanti.com/license
 * @link			http://www.openavanti.com
 * @version			0.6.7-beta
 *
 */
 
	/**
	 * Simple object to aid in create CSV documents
	 *
	 * @category	CSV
	 * @author		Kristopher Wilson
	 * @link		http://www.openavanti.com/docs/csv
	 */
	class CSV
	{
		public $aHeaders = array();	
		public $aData = array();
		
		
		/**
		 *
		 *
		 */		 		 		
		public function AddHeader( $sHeader )
		{
			$this->aHeaders[] = $sHeader;
			
		} // AddHeader()
		
		
		/**
		 *
		 *
		 */		 		 		
		public function AddData( $aData )
		{
			$this->aData[] = $aData;
			
		} // AddData()
		
		
		/**
		 *
		 *
		 */		 		 
		public function __toString()
		{
			$sData = implode( ",", $this->aHeaders ) . "\n";
			
			foreach( $this->aData as $aData )
			{
				$sDataRow = "";
				
				foreach( $aData as $sDataElement )
				{
					$sDataElement = str_replace( array( "\n", "\"" ), 
						array( " ", "\"\"" ), $sDataElement );
					
					$sDataRow .= !empty( $sDataRow ) ? "," : "";
					$sDataRow .= "\"{$sDataElement}\"";
				}
				
				$sData .= "{$sDataRow}\n";
			}
			
			return( $sData );
		
		} // __toString()
	
	} // CSV()

?>
