<?php

	class Validation
	{
		private $errors = array();
		
		public function __construct()
		{
		
		} 
		
		public function hasErrors()
		{
			return( count( $this->errors ) );
		}
		
		public function getErrors()
		{
			return( $this->errors );
		}
		
		public function clear()
		{
			$this->errors = array();
		}
		
		
		public function validateInteger( $value, $error )
		{
			if( !filter_var( $value, FILTER_VALIDATE_INT ) )
			{
				$this->errors[] = $error;
			}
		}
		
		
		
		
	};

?>
