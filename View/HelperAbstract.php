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
 
namespace OpenAvanti\View;

/**
 * View Helper abstract class implementation
 */
abstract class HelperAbstract
{
    /**
     * Stores a reference to the view object.
     *
     * @var \OpenAvanti\View
     */
    protected $_view = null;
    
    /**
     * Basic construction stores a reference to the view within this
     * object.
     * 
     * @param \OpenAvanti\View $view
     */
    public function __construct($view)
    {
        $this->_view = $view;
    }
    
    /**
     * Returns the view object.
     *
     * @return \OpenAvanti\View;
     */
    protected function getView()
    {
        return $this->_view;
    }
}
