<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author      Kristopher Wilson <kwilson@shuttlebox.net>
 * @copyright   Copyright (c) 2007-2010, Kristopher Wilson
 * @package     openavanti
 * @license     http://www.openavanti.com/license
 * @link        http://www.openavanti.com
 * @version     SVN: $Rev$ $LastChangedDate$
 */

namespace OpenAvanti;
 
/**
 * Controller Helper abstract implementation
 *
 * @category    Controller
 * @author      Kristopher Wilson <kwilson@shuttlebox.net>
 * @package     openavanti
 */
abstract class ControllerActionHelper
{
    /**
     *
     */
    protected $_controller = null;

    /**
     * 
     * @param \OpenAvanti\Controller $controller
     */
    public function __construct($controller)
    {
        $this->_controller = $controller;
    }
} 
