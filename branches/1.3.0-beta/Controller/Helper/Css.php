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

namespace OpenAvanti\Controller\Helper;

/**
 * This controller helper stores CSS files within the view object so that
 * the CSS view helper can load them into the view. Provides an easy mechanism
 * for dynamically adding module or controller specific view files on the fly.
 */
class Css extends \OpenAvanti\Controller\HelperAbstract
{
    /**
     * Loads the view file, passes the argument and returns the rendered
     * file.
     * 
     * @param string $file The path to the view file
     * @param array $args The view args to setup for the view file
     * @return string
     */
    public function process($file, $args = array())
    {
        $view = $this->_controller->getView();
        
        if (!isset($view->css)) {
            $view->css = array();
        }
        
        $view->css[] = $file;
    }
}
