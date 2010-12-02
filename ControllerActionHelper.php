<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson <kwilson@shuttlebox.net>
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @package         openavanti 
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */

namespace OpenAvanti;
 
/**
 * Controller Helper abstract implementation
 *
 * @category    Controller
 * @author      Kristopher Wilson <kwilson@shuttlebox.net>
 * @package     openavanti
 * @link        http://www.openavanti.com/documentation/1.4.0/ControllerActionHelper
 */
abstract class ControllerActionHelper
{
    /**
     *
     */
    protected $_controller = null;
    
    
    /**
     *
     *
     */
    public function __construct(&$controller)
    {
        $this->_controller = &$controller;
    }
    
    
    /**
     *
     *
     */
    protected function &getController()
    {
        return $this->_controller;
    }
}


