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

namespace OpenAvanti\View\Helper;

/**
 * A view helper for loading a partial with data and returning the rendered
 * output
 */
class Partial extends \OpenAvanti\View\HelperAbstract
{
    /**
     * Loads the view file, passes the argument and returns the rendered
     * file.
     * 
     * @param string $file The path to the view file
     * @param array $args The view args to setup for the view file
     * @return string
     */
    public function render($file, $args = array())
    {
        $view = new \OpenAvanti\View($this->_view->getController(), $file);
        $view->disableLayout();
        $view->setData($args);
        
        return $view->renderPage();
    }
}
