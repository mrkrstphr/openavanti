<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson <kwilson@shuttlebox.net>
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */
 
namespace OpenAvanti\View;

/**
 * View Helper abstract class implementation
 *
 * @category    View
 * @author      Kristopher Wilson <kwilson@shuttlebox.net>
 * @link        http://www.openavanti.com/docs/viewhelper
 */
abstract class HelperAbstract
{
    /**
     *
     */
    protected $_view = null;
    
    /**
     *
     *
     */
    public function __construct(&$view)
    {
        $this->_view = &$view;
    }
    
    /**
     *
     *
     */
    protected function &getView()
    {
        return $this->_view;
    }
}
