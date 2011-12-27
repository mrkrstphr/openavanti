<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson <kwilson@shuttlebox.net>
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @package         openavanti 
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */

namespace OpenAvanti;
 
/**
 * Handles application execution, sets up an autoloader, provides a mechanism for storing
 * application configuration and provides for standard request dispatching.
 * 
 * @category    Controller
 * @author      Kristopher Wilson <kwilson@shuttlebox.net>
 * @package     openavanti
 * @link        http://www.openavanti.com/documentation/1.4.0/Application
 */
class Application
{
    /**
     *
     */
    protected $_namespace = '';
    
    /**
     *
     */
    protected $defaultModule = '';
    
    /**
     *
     */
    protected $_applicationPath = '';
    
    /**
     * Stores the path to the non modular application controllers
     */
    protected $_controllerPath = "";
    
    /**
     * Stores the path to the application models
     */
    protected $_modelPath = "";
    
    /**
     * Stores the path to the application modules
     */
    protected $_modulePath = "";
   
    /**
     * Stores the currently loaded module
     */
    protected $_currentModule = "";

    /**
     * Stores the path to the non modular application forms
     */
    protected $_formPath = "";
    
    /**
     * Stores the path to the non modular application layouts
     */
    protected $_layoutPath = "";
    
    /**
     * Stores the path tot he non modular application views
     */
    protected $_viewPath = "";
    
    /**
     * Stores the path to the application libraries
     */
    protected $_libraryPath = "";
    
    /**
     * Stores a list of additional paths to autoload classes from
     */
    protected $_additionalAutoloadPaths = array();
    
    /**
     * Stores a reference to the dispatcher handling the HTTP request
     */
    protected $_dispatcher = null;

    /**
     * Stores the current working environment for the application
     */
    protected $_environment = null;

    /**
     * If true, the Dispatcher class will assume the first part of the URI is a module
     */
    protected $_useModules = false;

    
    /**
     * Constructor; sets up default paths, adds layout and view paths to
     * the include path and registers a default autoloader using the
     * default paths for controllers, models and the OpenAvanti library.
     */
    public function __construct()
    {
        // set our default assumptions about the paths. These can be
        // overridden by the developer
        
        if(!empty($_SERVER["DOCUMENT_ROOT"]))
            $documentRoot = $_SERVER["DOCUMENT_ROOT"];
        else
            $documentRoot = realpath(__DIR__ . "/../../public");
        
        $this->_modulePath = realpath("{$documentRoot}/../application/module");
        
        $this->_applicationPath = realpath($documentRoot . '/../application');
        
        $this->_controllerPath = realpath("{$documentRoot}/../application/controller");
        $this->_modelPath = realpath("{$documentRoot}/../application/model");
        $this->_modulePath = realpath("{$documentRoot}/../application/module");
        $this->_formPath = realpath("{$documentRoot}/../application/form");
        $this->_libraryPath = realpath("{$documentRoot}/../library/openavanti");
        $this->_layoutPath = realpath("{$documentRoot}/../application/layout");
        $this->_viewPath = realpath("{$documentRoot}/../application/view");
        
        // set our default autoloader
        
        spl_autoload_register(array($this, "defaultAutoloader"));
        
        $this->_dispatcher = new Dispatcher($this);
    }
    
    /**
     * Sets the current working environment of the application.
     *
     * @param string $environment The name of the environment
     */
    public function setEnvironment($environment)
    {
        $this->_environment = $environment;
    }

    
    /**
     *
     *
     */
    public function setIncludePath()
    {
        $includePath = explode(PATH_SEPARATOR, get_include_path());
        
        $includePath[] = $this->_layoutPath;
        $includePath[] = $this->_viewPath;
        
        set_include_path(implode(PATH_SEPARATOR, $includePath));
    }

    
    /**
     * Wraps the user init() method and sets the view include path after calling the init()
     * method in case the user deviates from the default directory structure.
     */
    protected function _init()
    {
        $this->init();
        
        // We don't want to set our include path until after user initialization has occurred:
        
        $this->setIncludePath();
    }
    
    
    /**
     * Provides a mechanism for user initialization of the Application class. This method is useful
     * for overriding the default locations of several resource paths.
     */
    public function init()
    {
        
    }
    
    /**
     *
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
    }
    
    /**
     *
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }
  

    /**
     * Determines whether or not a specified module exists
     *
     * @param string $module The module we're looking for
     * @return bool True if the specified module exists, false if not
     */
    public function moduleExists($module)
    {
        $module = "{$this->_modulePath}/{$module}";
         
        if(file_exists($module) && is_dir($module))
            return true;
         
        return false;
    }
 
   
    /**
     * Initialize a specified module. If a method is defined on the application in the format
     * of init[ModuleName]Module(), this method will be executed. The module will be stored as 
     * the currentModule, and it's layouts and views directories will be added to the include 
     * path.
     *
     * @param string $moduleName The name of the module
     */
    public function moduleInitialization($moduleName)
    {
        $this->_currentModule = $moduleName;

        if(is_callable(array($this, "init{$moduleName}Module")))
        {
            call_user_func(array($this, "init{$moduleName}Module"));
        }
        
        set_include_path(get_include_path() .
            PATH_SEPARATOR . "{$this->_modulePath}/{$moduleName}/layout" .
            PATH_SEPARATOR . "{$this->_modulePath}/{$moduleName}/view");
    }
    
    
    /**
     *
     *
     */
    public function getCurrentModule()
    {
        return $this->_useModules ? $this->_currentModule : null;
    }


    /**
     * Enables or disables the use of modules, which is disabled by default
     *
     * @param bool $enable Optional; True of modules should be enabled, false to disable.
     *      Default: true
     */
    public function setUseModules($use = true)
    {
        $this->_useModules = $use;
    }
    
    
    /**
     * Returns whether modules are being used
     *
     * @return bool True if modules are enabled, false if disabled
     */
    public function getUseModules()
    {
        return $this->_useModules;
    }
   
    /**
     *
     */
    public function setDefaultModule($defaultModule)
    {
        $this->_defaultModule = $defaultModule;
    }
    
    /**
     *
     */
    public function getDefaultModule()
    {
        return $this->_defaultModule;
    }
    
    /**
     * Sets the path to the controllers directory, which is used by the
     * default autoloader to load requested classes.
     *
     * @param string $path The path to the controllers directory
     */
    public function setControllerPath($path)
    {
        $this->_controllerPath = $path;
    }
    
    
    /**
     * Returns the path of the controllers directory.
     *
     * @return string The path of the controllers directory
     */
    public function getControllerPath()
    {
        return $this->_controllerPath;
    }
    
    
    /**
     * Sets the path to the models directory, which is used by the
     * default autoloader to load requested classes.
     *
     * @param string $path The path to the models directory
     */
    public function setModelPath($path)
    {
        $this->_modelPath = $path;
    }
    
    
    /**
     * Returns the path of the models directory.
     *
     * @return string The path of the models directory
     */
    public function getModelPath()
    {
        return $this->_modelPath;
    }
    
    
    /**
     * Sets the path to the OpenAvanti library directory, which is used by
     * the default autoloader to load requested classes.
     *
     * @param string $path The path to the library directory
     */
    public function setLibraryPath($path)
    {
        $this->_libraryPath = $path;
    }
    
    
    /**
     * Returns the path of the OpenAvanti library directory.
     *
     * @return string The path of the library directory
     */
    public function getLibraryPath()
    {
        return $this->_libraryPath;
    }
    
    
    /**
     * Sets the path of the layouts directory, which is also added to the
     * include path to aid in requiring these layout files.
     *
     * @param string $path The path of the layouts directory
     */
    public function setLayoutPath($path)
    {
        $this->_layoutPath = $path;
        
        $this->appendIncludePath($path);
    }
    
    
    /**
     * Returns the path of the layouts directory.
     *
     * @return string The path of the layouts directory
     */
    public function getLayoutPath()
    {
        return $this->_layoutPath;
    }
    
    
    /**
     * Sets the path of the views directory, which is also added to the
     * include path to aid in requiring these view files.
     *
     * @param string $path The path of the views directory
     */
    public function setViewPath($path)
    {
        $this->_viewPath = $path;
        
        $this->appendIncludePath($path);
    }
    
    
    /**
     * Returns the path of the views directory.
     *
     * @return string The path of the views directory
     */
    public function getViewPath()
    {
        return $this->_viewPath;
    }
    
    
    /**
     * Adds additional paths to OpenAvanti's default autloader to aid in
     * automatic class definition loading.
     *
     * @param array $paths An array of paths to add to the autoloader
     */
    public function addAdditionalAutoloadPaths(array $paths)
    {
        foreach($paths as $path)
        {
            $path = realpath($path);
            
            if(!empty($path))
                $this->_additionalAutoloadPaths[] = $path;
        }
    }
    
    
    /**
     * Adds a directory path to the include path to help with automatic
     * loading of files without the path name specified.
     *
     * @param string $path The path to add to the include path
     */
    public function appendIncludePath($path)
    {
        $path = realpath($path);
        
        if(is_dir($path))
            set_include_path(get_include_path() . PATH_SEPARATOR . $path);
    }
    
    
    /**
     * The OpenAvanti autoloader, responsible for automatically loading
     * class file definitions for the library, controllers and models, as
     * well as any other classes in paths added by the
     * addAdditionalAutoLoadPaths() method.
     *
     * Class file names are assumed to be in the format "ClassFile.php"
     *
     * @param string $className The name of the class to attempt to autoload
     */
    public function defaultAutoloader($className)
    {
        // normalize the class name for namespaces:
        $className = str_replace('\\', '/', $className);
        
        $namespace = substr($className, 0, strpos($className, '/'));
        $className = substr($className, strpos($className, '/') + 1);
        
        $candidates = array(
            $this->_applicationPath . '/module/' . $className . '.php',
            $this->_applicationPath . '/' . $className . '.php',
            realpath($this->_libraryPath . '/../') . '/' . strtolower($namespace) . '/' . $className . '.php'
        );
        
        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                require_once $candidate;
                return;
            }
        }
        
        return false;
    }
    
    
    /**
     * Returns a reference to the dispatcher used by this Application
     *
     * @return Dispatcher A reference to the dispatcher
     */
    public function &getDispatcher()
    {
        return $this->_dispatcher;
    }
    
    
    /**
     * Determines if an action helper class exists and, if so, loads the class file
     *
     * @param string $helper The name of the helper class to check for existance and load
     * @return bool True if the helper exists and is loaded, false otherwise
     */
    public function actionHelperExists($helper)
    {
        if(class_exists($helper, false) && is_subclass_of($helper, 'ControllerActionHelper'))
        {
            return true;
        }
        else if(file_exists($this->_controllerPath . '/helpers/' . ucfirst($helper) . '.php'))
        {
            require_once $this->_controllerPath . '/helpers/' . ucfirst($helper) . '.php';
            return true;
        }
        else if(file_exists("{$this->_modulePath}/{$this->_currentModule}/controllers/helpers/" . ucfirst($helper) . '.php'))
        {
            require_once "{$this->_modulePath}/{$this->_currentModule}/controllers/helpers/" . ucfirst($helper) . '.php';
            return true;
        }
        
        return false;
    }
    
    
    /**
     * Determines if a view helper class exists and, if so, loads the class file
     *
     * @param string $helper The name of the helper class to check for existance and load
     * @return mixed Returns the namespaced class name for the found class
     *      file, or false if not found
     */
    public function viewHelperExists($helper)
    {
        $candidates = array(
            '\\' . $this->getNamespace() . '\\' . $this->getCurrentModule() . '\\view\\helper\\' . $helper,
            '\\' . $this->getNamespace() . '\\view\\helper\\' . $helper,
            '\\OpenAvanti\\View\\Helper\\' . $helper
        );
        
        foreach ($candidates as $candidate) {
            if (class_exists($candidate) && is_subclass_of($candidate, '\\OpenAvanti\\View\\HelperAbstract')) {
                return $candidate;
            }
        }
        
        return false;
    }
    
    /**
     *
     *
     */
    public function initEnvironment()
    {
        if(!empty($this->_environment) && method_exists($this, "init" . $this->_environment))
        {
            $init = "init" . $this->_environment;
            $this->$init();
        }
    }
    
    
    /**
     * Returns the base directory for the current URI. This will be the same as the current
     * module string if using modules, or this will be null if modules are not being used or
     * if the request is handled by the default module.
     *
     * @return string The base directory of the request
     */
    public function getBaseDir()
    {
        if(empty($this->_currentModule))
            return null;
        
        return $this->_currentModule . "/";
    }
    
    
    /**
     * Parses the Uri and passes it along to the dispatcher for processing.
     * The query string is stripped out of the request uri for processing.
     */
    public function run()
    {
        $this->_init();
        
        $this->initEnvironment();

        $uri = str_replace("?" . $_SERVER["QUERY_STRING"], "", $_SERVER["REQUEST_URI"]);
        $uri = $uri != "/" ? $uri : "index";
        
        $this->_dispatcher->connect($uri);
        
    }
    
}


