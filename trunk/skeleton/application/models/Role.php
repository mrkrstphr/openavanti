<?php

use OpenAvanti\Validation;

class Role extends OpenAvanti\Db\Model
{
    const StatusActive = 'active';
    const StatusInactive = 'inactive';
    
    public static $_statuses = array(
        self::StatusActive => 'Active',
        self::StatusInactive => 'Inactive'
    );
    
    
    /**
     * Validation rules for Roles
     *
     * @returns bool Whether validation passed (true) or failed (false)
     */
    public function validate()
    {			
        Validation::validateLengthRange("name", $this->name, 3, 32);
        Validation::validateInteger("permission", $this->permission);

        return !Validation::hasErrors();
        
    } // validate()

} // Role()

?>
