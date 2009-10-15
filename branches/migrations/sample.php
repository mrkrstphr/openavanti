<?php

	function __autoload( $sClass )
	{			
		$sFileName = "class." . strtolower( $sClass ) . ".php";
    
        
        if( file_exists( $sFileName ) )
        {
            require_once( $sFileName );
            return;
        }
		
	} // __autoload()
	
	
	Database::AddProfile( array(
		"driver" => "postgres",
		"name" => "vingo",
		"user" => "postgres",
		"password" => ""
	) );
	
	$oDatabase = Database::GetConnection();
    
    $oDatabase->CreateTable( "customers" );
    
    $oDatabase->AddColumn( "customers", "customer_id", "serial", array( "primary" => true ) );
    $oDatabase->AddColumn( "customers", "first_name", "varchar", array( "size" => 32, "null" => false ) );
    $oDatabase->AddColumn( "customers", "last_name", "varchar", array( "size" => 32, "null" => false ) );
    $oDatabase->AddColumn( "customers", "status", "varchar", array( "size" => 16, "null" => false, "default" => "active" ) );
    $oDatabase->AddColumn( "customers", "user_id", "int" );
    
    
    
    return;

    class CreateInitialSchema extends DatabaseMigration
    {
    
        public function up()
        {
            $this->CreateTable( "users", "username" );
            
            $this->AddColumn( "users", "user_id", "serial", array(
                "null" => false, 
                "primary" => true
            ) );
            $this->AddColumn( "users", "first_name", "varchar", array(
                "size" => 32,
                "null" => false
            ) );
            $this->AddColumn( "users", "last_name", "varchar", array(
                "size" => 32,
                "null" => false
            ) );
            $this->AddColumn( "users", "email", "varchar", array(
                "size" => 64,
                "null" => false
            ) );
            $this->AddColumn( "users", "username", "varchar", array(
                "size" => 32,
                "null" => false
            ) );
            $this->AddColumn( "users", "password", "varchar", array(
                "size" => 32,
                "null" => false
            ) );
            $this->AddColumn( "users", "created_timestamp", "timestamp", array(
                "default" => "now()"
            ) );
            $this->AddColumn( "users", "created_by_id", "int", array(
                "references" => "users"
            ) );
            
            $this->AddColumn( "customers", "created_by_id", "int", array(
                "references" => "users"
            ) );
            
            $this->RemoveColumn( "customers", "created_by_name" );

            
            /*$oCustomers = new Customer();
            $oCustomers->Find();
            
            foreach( $oCustomers as $oCustomer )
            {
                $oCustomer->created_by_id = 1;
                $oCustomer->Save();
            }*/
        }
        
        
        public function down()
        {
            $this->AddColumn( "customers", "created_by_name", "varchar", array(
                "size" => 32
            ) );
            
            /*$oCustomers = new Customer();
            $oCustomers->Find();
            
            foreach( $oCustomers as $oCustomer )
            {
                $oCustomer->created_by_name = $oCustomer->created_by->first_name . 
                    " " . $oCustomer->created_by->last_name;
                $oCustomer->Save();
            }*/
            
            $this->RemoveColumn( "customers", "created_by_id" );
            $this->DropTable( "users" );
        }
    
    
    
        // $oObj = $this->CreateTable( table_name, owner );
        // $this->DropTable( table_name );
        // $this->RenameTable( old_name, new_name );
        // $this->AddColumn( table_name, column_name, type [, options] );
        // $this->RenameColumn( table_name, old_name, new_name );
        // $this->AlterColumn( table_name, column_name, type [, options ] );
        // $this->RemoveColumn( table_name, column_name );
        // $this->AddForeignKey( table_name, column_name, reference_table_name, reference_column_name [, actions ] );
        // $this->DropForeignKey( table_name, column_name );
        // $this->CreateSequence( sequence_name );
        
        // throw new IrreversibleMigrationException();
    
    }




    $oMigration = new CreateInitialSchema();
    
    $oMigration->Up();
    
    $oMigration->Down();

?>
