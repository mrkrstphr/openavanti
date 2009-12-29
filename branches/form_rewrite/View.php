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
 * @version         1.3.0-beta
 */
 
 
    /**
     * A View class for handling the UI
     *
     * @category    Controller
     * @author      Kristopher Wilson
     * @link        http://www.openavanti.com/docs/view
     */
    class View
    {
        // Stores a reference to the controller that is preparing this view:
        protected $_controller = null;
        
        // Stores data used by the actual view file for redering:
        public $_data = array();
        
        // Stores the default layout to use if no other is specified:
        public static $_defaultLayout = "";
        
        // Stores the layout to render, overriding any default specified:
        public $_layout = "";
        
        // Stores the view file to render inside the layout through 
        // GetContent():
        protected $_viewScript = "";
        
        public static $_viewFileExtension = ".php";
        
        // Toggles whether to render the layout:
        public $_renderLayout = true;
        
        // Toggles whether to render the view:
        public $_renderView = true;
        
        
        /**
         * The final constructor; sets up data for the controller and calls 
         * init()
         * 
         * @final
         * @argument Dispatcher The dispatcher class that loaded this controller
         * @argument string Optional; the name of the view file to render
         * @returns void
         */
        public final function __construct(&$controller, $viewFileName = "")
        {
            $this->_controller = &$controller;
            
            $this->_viewScript = $viewFileName;

            $this->init();
            
        } // __construct()
        
        
        /**
         * Provides initialization mechanism for the View class and is called 
         * by the constructor. Subclasses cannot override the constructor due 
         * to the possibility of not passing the correct required parameters.
         * 
         * @returns void
         */
        public function init()
        {
            
        } // init()
        
        
        
        /**
         * Returns a copy of the Controller class that invoked this view
         * 
         * @returns Dispatcher The Controller class
         */
        public function &getController()
        {
            return $this->_controller;
        
        } // getController()
        
        
        /**
         * Sets the layout file to use for this controller. 
         * 
         * @argument string The file name of the layout file that should be 
         *      rendered
         * @returns void
         */
        public function setLayout($layoutFile)
        {
            $this->_layout = $layoutFile;
            
        } // setLayout()
        
        
        /**
         * 
         * @static 
         * @argument String The file name of the layout that should be 
         *      rendered by default if no other layout file is specified
         * @returns void
         */
        public static function setDefaultLayout($layoutFile)
        {
            self::$_defaultLayout = $layoutFile;
            
        } // setDefaultLayout()
        
        
        /**
         *
         * @static
         * @param
         * @return
         */
        public static function setViewFileExtension($extension)
        {
            self::$_viewFileExtension = $extension;
            
        } // setViewFileExtension()
        
        
        /**
         * Sets the view file that should be loaded at the end of the request. 
         * This method does not check to ensure that the file specified 
         * actually exists. It is up to the code that loads the view file to 
         * do this (normally the Dispatcher class).                 
         *       
         * @argument string The file name of the view file that should be 
         *      loaded.
         * @returns void
         */ 
        public function setViewScript($view)
        {
            $this->_viewScript = $view;
        
        } // setView()
        
        // TODO document
        
        /**
         *
         *
         */
        public function getViewScript()
        {
            return $this->_viewScript;
        
        } // getViewScript()
        
        
        /**
         * Responsible for rendering the page, or the layout file specifically. 
         * The view file will be rendered when GetContent() is called by
         * the layout. 
         * 
         * @returns void
         */
        public function renderPage()
        {
            if($this->_renderLayout)
            {
                if(!empty($this->_layout))
                {
                    if(FileFunctions::fileExistsInPath($this->_layout))
                    {
                        require($this->_layout);
                    }
                    else
                    {
                        throw new Exception(ErrorHandler::VIEW_NOT_FOUND);
                    }
                }
                else if(!empty(self::$_defaultLayout))
                {
                    if(FileFunctions::fileExistsInPath(self::$_defaultLayout))
                    {
                        require(self::$_defaultLayout);
                    }
                    else
                    {
                        throw new Exception(ErrorHandler::VIEW_NOT_FOUND);
                    }
                }
            }
            else if($this->_renderView)
            {
                return $this->renderContent();
            }

        } // renderPage()


        /**
         * Called from the layout file to render the action specific view file 
         * into the layout.
         * 
         * @returns void
         */
        public function renderContent()
        {
            if($this->_renderView)
            {
                if(($view = FileFunctions::fileExistsInPath($this->_viewScript)) !== false)
                {
                    require($view);
                }
                else
                {
                    throw new Exception(ErrorHandler::VIEW_NOT_FOUND);
                }
            }
            
        } // renderContent()
        
        
        /**
         * Disables rendering of the layout. The view will still be rendered,
         * unless it is also disabled.
         * 
         * @argument bool Optional; Should the layout be disabled? Default: true
         * @returns void
         */
        public function disableLayout($disable = true)
        {
            $this->_renderLayout = !$disable;
            
        } // disableLayout()
        
        
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
            
        } // disableView()
        
        
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
            
        } // disableAllRendering()
        
        
        /**
         * Handles internal errors that occur in processing by passing the
         * error off to the dispatcher, which handles all errors throughout
         * the HTTP request process.
         * 
         * @argument string The error code to handle
         * @returns void
         */
        protected function handleError($errorCode)
        {
            $this->oController->getDispatcher()->handleError($errorCode);
            
        } // handleError()
        
        
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
            
        } // __get()
        
        
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
            
        } // __set()
        
        
        /**
         * 
         * 
         */
        public function __isset($name)
        {
            return isset($this->_data[$name]);

        } // __isset()

    } // View()

?>
