<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.3.0-beta
 */

namespace OpenAvanti\View\Helper;

/**
 * A view helper for loading a partial with data and returning the rendered
 * output
 */
class Css extends \OpenAvanti\View\HelperAbstract
{
    /**
     * Loads the view file, passes the argument and returns the rendered
     * file.
     * 
     * @param string $file The path to the view file
     * @param array $args The view args to setup for the view file
     * @return string
     */
    public function render()
    {
        $output = '';
        if (isset($this->_view->css) && is_array($this->_view->css)) {
            foreach ($this->_view->css as $css) {
                $output .= '<link rel="stylesheet" type="text/css" href="' . $css . '" />' . "\n";
            }
        }
        
        return $output;
    }
}
