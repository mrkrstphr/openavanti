<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5.3+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2012, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.3.0-beta
 */

namespace OpenAvanti\Controller;
 
/**
 * Controller Helper abstract implementation
 */
abstract class HelperAbstract
{
    /**
     * Stores a reference to the controller.
     *
     * @var \OpenAvanti\Controller
     */
    protected $_controller = null;

    /**
     * Basic construction stores a reference to the controller within this
     * object.
     * 
     * @param \OpenAvanti\Controller $controller
     */
    public function __construct($controller)
    {
        $this->_controller = $controller;
    }
    
    /**
     * Returns the controller object.
     *
     * @return \OpenAvanti\Controller;
     */
    protected function getController()
    {
        return $this->_controller;
    }
} 
