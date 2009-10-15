<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.0.5
 *
 */


    /**
     * DatabaseMigration class definition
     *
     * @category    Database
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/databasemigration
     */
    class DatabaseMigration
    {
        
        /**
         * 
         * 
         */
        public function Up() 
        {
            
        } // Up()
        
        
        /**
         * 
         * 
         */
        public function Down() 
        { 
            
        } // Down()
        
        
        /**
         * 
         * 
         */
        protected function CreateTable( $sTableName, $sOwner ) 
        { 
            
            
        } // CreateTable()
        
        
        /**
         * 
         * 
         */
        protected function DropTable( $sTableName )
        {
            
        } // DropTable()
        
        
        /**
         * 
         * 
         */
        protected function RenameTable( $sOldTableName, $sNewTableName )
        {
            
        } // RenameTable()
        
        
        /**
         * 
         * 
         */
        protected function AddColumn( $sTableName, $sColumnName, $sColumnType, $aOptions = null ) 
        {
            
        } // AddColumn()
        
        
        /**
         * 
         * 
         */
        protected function RemoveColumn( $sTableName, $sColumnName )
        {
            
        } // RemoveColumn()
        
        
        /**
         * 
         * 
         */
        protected function RenameColumn( $sTableName, $sOldColumnName, $sNewColumnName )
        {
            
        } // RenameColumn()
        
        
        /**
         * 
         * 
         */
        protected function AlterColumn( $sTableName, $sColumnName, $sType, $aOptions = null )
        {
            
        } // AlterColumn()
        
        
        /**
         * 
         * 
         */
        protected function AddForeignKey( $sTableName, $sColumnName, $sRefTableName, $sRefColumnName, $aActions = null )
        {
            
        } // AddForeignKey()
        
        
        /**
         * 
         * 
         */
        protected function DropForeignKey( $sTableName, $sColumnName )
        {
            
        } // DropForeignKey()
        
        
        /**
         * 
         * 
         */
        protected function CreateSequence( $sSequenceName )
        {
            
        } // CreateSequence()
        
        
    } // DatabaseMigration()
    
?>
