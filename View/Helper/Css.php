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
 * A view helper for pulling dynamically added CSS files from the View object
 * and generating HTML to inject into the header for those CSS files.
 */
class Css extends \OpenAvanti\View\HelperAbstract
{
    /**
     * Retrieves the list of CSS files in the view and generates HTML for each
     * one, returning the string.
     * 
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
