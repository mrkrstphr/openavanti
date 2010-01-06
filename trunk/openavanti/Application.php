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
 
 
/**
 * Handles application execution, sets up an autoloader, provides a
 * mechanism for storing application configuration and provides for
 * standard request dispatching.
 * 
 * @category    Controller
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/application
 */
class Application
{
    /**
     *
     */
    protected $_controllerPath = "";
    
    /**
     *
     */
    protected $_modelPath = "";
    
    /**
     *
     */
    protected $_formPath = "";
    
    /**
     *
     */
    protected $_layoutPath = "";
    
    /**
     *
     */
    protected $_viewPath = "";
    
    /**
     *
     */
    protected $_libraryPath = "";
    
    /**
     *
     */
    protected $_additionalAutoloadPaths = array();
    
    /**
     *
     */
    protected $_dispatcher = null;
    
    
    /**
     * Constructor; sets up default paths, adds layout and view paths to
     * the include path and registers a default autoloader using the
     * default paths for controllers, models and the OpenAvanti library.
     */
    public function __construct()
    {
        // set our default assumptions about the paths. These can be
        // overridden by the developer
        
        $documentRoot = $_SERVER["DOCUMENT_ROOT"];
        
        $this->_controllerPath = realpath("{$documentRoot}/../application/controllers");
        $this->_modelPath = realpath("{$documentRoot}/../application/models");
        $this->_formPath = realpath("{$documentRoot}/../application/forms");
        $this->_libraryPath = realpath("{$documentRoot}/../application/library/openavanti");
        $this->_layoutPath = realpath("{$documentRoot}/../application/layouts");
        $this->_viewPath = realpath("{$documentRoot}/../application/views");
        
        $includePath = get_include_path() . PATH_SEPARATOR .
            $this->_layoutPath . PATH_SEPARATOR .
            $this->_viewPath;
        
        set_include_Path($includePath);
        
        // set our default autoloader
        
        spl_autoload_register(array($this, "defaultAutoloader"));
        
        $this->_dispatcher = new Dispatcher();
        
    } // __construct()
    
    
    /**
     * Sets the path to the controllers directory, which is used by the
     * default autoloader to load requested classes.
     *
     * @param string $path The path to the controllers directory
     * @return void
     */
    public function setControllerPath($path)
    {
        $this->_controllerPath = $path;
        
    } // setControllerPath()
    
    
    /**
     * Returns the path of the controllers directory.
     *
     * @return string The path of the controllers directory
     */
    public function getControllerPath()
    {
        return $this->_controllerPath;
        
    } // getControllerPath()
    
    
    /**
     * Sets the path to the models directory, which is used by the
     * default autoloader to load requested classes.
     *
     * @param string $path The path to the models directory
     * @return void
     */
    public function setModelPath($path)
    {
        $this->_modelPath = $path;
        
    } // setModelPath()
    
    
    /**
     * Returns the path of the models directory.
     *
     * @return string The path of the models directory
     */
    public function getModelPath()
    {
        return $this->_modelPath;
        
    } // getModelPath()
    
    
    /**
     * Sets the path to the OpenAvanti library directory, which is used by
     * the default autoloader to load requested classes.
     *
     * @param string $path The path to the library directory
     * @return void
     */
    public function setLibraryPath($path)
    {
        $this->_libraryPath = $path;
        
    } // setLibraryPath()
    
    
    /**
     * Returns the path of the OpenAvanti library directory.
     *
     * @return string The path of the library directory
     */
    public function getLibraryPath()
    {
        return $this->_libraryPath;
        
    } // getLibraryPath()
    
    
    /**
     * Sets the path of the layouts directory, which is also added to the
     * include path to aid in requiring these layout files.
     *
     * @param string $path The path of the layouts directory
     * @return void
     */
    public function setLayoutPath($path)
    {
        $this->_layoutPath = $path;
        
        $this->appendIncludePath($path);
        
    } // setLayoutPath()
    
    
    /**
     * Returns the path of the layouts directory.
     *
     * @return string The path of the layouts directory
     */
    public function getLayoutPath()
    {
        return $this->_layoutPath;
        
    } // getLayoutPath()
    
    
    /**
     * Sets the path of the views directory, which is also added to the
     * include path to aid in requiring these view files.
     *
     * @param string $path The path of the views directory
     * @return void
     */
    public function setViewPath($path)
    {
        $this->_viewPath = $path;
        
        $this->appendIncludePath($path);
        
    } // setViewPath()
    
    
    /**
     * Returns the path of the views directory.
     *
     * @return string The path of the views directory
     */
    public function getViewPath()
    {
        return $this->_viewPath;
        
    } // getViewPath()
    
    
    /**
     * Adds additional paths to OpenAvanti's default autloader to aid in
     * automatic class definition loading.
     *
     * @param array $paths An array of paths to add to the autoloader
     * @return void
     */
    public function addAdditionalAutoloadPaths(array $paths)
    {
        foreach($paths as $path)
        {
            $path = realpath($path);
            
            if(!empty($path))
            {
                $this->_additionalAutoloadPaths[] = $path;
            }
        }
        
    } // addAdditionalAutoloadPaths()
    
    
    /**
     * Adds a directory path to the include path to help with automatic
     * loading of files without the path name specified.
     *
     * @param string $path The path to add to the include path
     * @return void
     */
    public function appendIncludePath($path)
    {
        $path = realpath($path);
        
        if(is_dir($path))
        {
            set_include_path(get_include_path() . PATH_SEPARATOR . $path);
        }
        
    } // appendIncludePath()
    
    
    /**
     * The OpenAvanti autoloader, responsible for automatically loading
     * class file definitions for the library, controllers and models, as
     * well as any other classes in paths added by the
     * addAdditionalAutoLoadPaths() method.
     *
     * Class file names are assumed to be in the format "ClassFile.php"
     *
     * @param string $className The name of the class to attempt to autoload
     * @return void
     */
    public function defaultAutoloader($className)
    {
        $fileName = "{$className}.php";
        
        $paths = array(
            $this->_controllerPath,
            $this->_modelPath,
            $this->_formPath,
            $this->_libraryPath,
            $this->_libraryPath . '/Form',
            $this->_libraryPath . '/Form/Element'
        );
        
        $paths = array_merge($paths, $this->_additionalAutoloadPaths);
        
        foreach($paths as $path)
        {
            $candidate = "{$path}/{$fileName}";
            
            if(file_exists($candidate))
            {
                require($candidate);
                return;
            }
        }
        
    } // defaultAutoloader()
    
    
    /**
     * Returns a reference to the dispatcher used by this Application
     *
     * @return Dispatcher A reference to the dispatcher
     */
    public function &getDispatcher()
    {
        return $this->_dispatcher;
        
    } // getDispatcher()
    
    
    /**
     * Parses the Uri and passes it along to the dispatcher for processing.
     * The query string is stripped out of the request uri for processing.
     *
     * @return void
     */
    public function run()
    {
        $uri = str_replace("?" . $_SERVER["QUERY_STRING"], "", $_SERVER["REQUEST_URI"]);
        $uri = $uri != "/" ? $uri : "index";
        
        $this->_dispatcher->connect($uri);
        
    } // run()
    
} // Application()

?>
