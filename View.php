<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */

namespace OpenAvanti;

use \Exception;

 
/**
 * A View class for handling the UI
 *
 * @category    Controller
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/view
 */
class View
{
    /**
     * Stores a reference to the controller that is preparing this view:
     */
    protected $_controller = null;
    
    /**
     * Stores data used by the actual view file for redering:
     */
    protected $_data = array();
    
    /**
     * Stores the default layout to use if no other is specified:
     */
    public static $_defaultLayout = "";
    
    /**
     * Stores the layout to render, overriding any default specified:
     */
    protected $_layout = "";
    
    /**
     * Stores the view file to render inside the layout through GetContent():
     */
    protected $_viewScript = "";
    
    /**
     *
     */
    public static $_viewFileExtension = ".php";
    
    /**
     * Toggles whether to render the layout:
     */
    protected $_renderLayout = true;
    
    /**
     * Toggles whether to render the view:
     */
    protected $_renderView = true;
    
    
    protected $_isBuffering = false;
    
    
    /**
     * The final constructor; sets up data for the controller and calls 
     * init()
     * 
     * @final
     * @param Controller $controller The controller class that is prepairing this view
     * @param string $viewFileName Optional; the name of the view file to render
     */
    public final function __construct(&$controller, $viewFileName = "")
    {
        $this->_controller = &$controller;
        
        $this->_viewScript = $viewFileName;

        // If this is an AJAX request, let's be assumptious and disable
        // the layout:
        
        if($this->_controller->getRequest()->isAjaxRequest())
            $this->disableLayout();

        $this->init();
    }
    
    
    /**
     * Provides initialization mechanism for the View class and is called 
     * by the constructor. Subclasses cannot override the constructor due 
     * to the possibility of not passing the correct required parameters.
     */
    public function init()
    {
        
    }
    
    
    /**
     * Returns a copy of the Controller class that invoked this view
     * 
     * @return Dispatcher The Controller class
     */
    public function &getController()
    {
        return $this->_controller;
    }
    
    
    /**
     * Sets the layout file to use for this controller. 
     * 
     * @param string $layoutFile The file name of the layout file that should be 
     *      rendered
     */
    public function setLayout($layoutFile)
    {
        $this->_layout = $layoutFile;
    }
    
    
    /**
     * 
     * @static 
     * @param string $layoutFile The file name of the layout that should be 
     *      rendered by default if no other layout file is specified
     */
    public static function setDefaultLayout($layoutFile)
    {
        self::$_defaultLayout = $layoutFile;
    } 
    
    
    /**
     *
     * @static
     * @param
     */
    public static function setViewFileExtension($extension)
    {
        self::$_viewFileExtension = $extension;
    }
    
    
    /**
     * Sets the view file that should be loaded at the end of the request. 
     * This method does not check to ensure that the file specified 
     * actually exists. It is up to the code that loads the view file to 
     * do this (normally the Dispatcher class).                 
     *       
     * @param string $view The file name of the view file that should be 
     *      loaded.
     */ 
    public function setViewScript($view)
    {
        $this->_viewScript = $view;
    }
    
    
    /**
     * Retrieves the name of the view file that will be rendered for the request
     *
     * @return string The name of the view file to be rendered
     */
    public function getViewScript()
    {
        return $this->_viewScript;
    }
    
    
    /**
     * Responsible for rendering the page, or the layout file specifically. 
     * The view file will be rendered when renderContent() is called by
     * the layout. 
     * 
     * @returns string The rendered page
     */
    public function renderPage()
    {
        ob_start();
        
        $this->_isBuffering = true;

        $contents = '';
        
        if($this->_renderLayout)
        {
            if(!empty($this->_layout))
            {
                if(\OpenAvanti\Util\File::fileExistsInPath($this->_layout))
                {
                    require $this->_layout;
                }
                else
                {
                    throw new LayoutNotFoundException("Layout {$this->_layout} not found.");
                }
            }
            else if(!empty(self::$_defaultLayout))
            {
                if(\OpenAvanti\Util\File::fileExistsInPath(self::$_defaultLayout))
                {
                    require self::$_defaultLayout;
                }
                else
                {
                    throw new LayoutNotFoundException("Layout {$this->_layout} not found.");
                }
            }
        }
        else if($this->_renderView)
        {
            $this->renderContent();
        }
        
        $contents .= ob_get_contents();
        
        ob_end_clean();
        
        $this->_isBuffering = false;
        
        
        return $contents;
    }


    /**
     * Called from the layout file to render the action specific view file 
     * into the layout.
     */
    public function renderContent()
    {
        if($this->_renderView)
        {
            if(($view = \OpenAvanti\Util\File::fileExistsInPath($this->_viewScript)) !== false)
            {
                require $view;
            }
            else
            {
                if($this->_isBuffering)
                {
                    ob_clean();
                    $this->_isBuffering = false;
                }
                
                throw new ViewNotFoundException("View file {$this->_viewScript} not found.");
            }
        }
    }
    
    
    /**
     * Disables rendering of the layout. The view will still be rendered,
     * unless it is also disabled.
     * 
     * @argument bool Optional; Should the layout be disabled? Default: true
     */
    public function disableLayout($disable = true)
    {
        $this->_renderLayout = !$disable;
    }
    
    
    /**
     * Disables rendering of the view. The layout will still be rendered,
     * unless it is also disabled.
     * 
     * @argument bool Optional; Should the view be disabled? Default: true
     * @returns void
     */
    public function disableView($disable = true)
    {
        $this->_renderView = !$disable;
    }
    
    
    /**
     * Disables rendering of the layout and view no other output will be
     * displayed unless the code explicitly provides output.
     * 
     * @argument bool Optional; Should the layout and view be disabled? 
     *      Default: true
     * @returns void
     */
    public function disableAllRendering($disable = true)
    {            
        $this->disableLayout($disable);
        $this->disableView($disable);
    }
    
    
    /**
     * Used by the view file to get a data variable, which are stored in 
     * the _data array and are settable through __set(), usually by the
     * Controller.
     * 
     * 
     * @argument string The name of the data variable being retrieved
     * @returns void
     */
    public function __get($name)
    {
        if(isset($this->_data[$name]))
        {
            return $this->_data[$name];
        }
        
        return null;
    }
    
    
    /**
     * Sets a view file data variable to be used by the view file, and is
     * usually called by the Controller.
     * 
     * @argument string The name of the data variable being set
     * @argument string The value of the data variable being set
     * @returns void
     */
    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }
    
    
    /**
     * 
     * 
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }
    
    
    /**
     * __call() magic method setup to load view helpers as requested
     *
     * @param string $method The method being called, which translates to the helper class
     * @param array $arguments An array of arguments to pass to the render() method of the helper
     * @return mixed The return value of the helper, if any
     */
    public function __call($method, $arguments)
    {
        $method = $method . 'Helper';
        
        if($this->getController()->getApplication()->viewHelperExists($method))
        {
            $method = new $method($this);
            return call_user_func_array(array($method, 'render'), $arguments);
        }
    }

} 

