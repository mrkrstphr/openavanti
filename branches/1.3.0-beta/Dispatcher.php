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
 * Dispatcher to route URI request to appropriate controller / method, and loads view files
 * based on instructions from the controller, passing data setup by the controller from the
 * controller to the view file.          
 *
 * @category    Database
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/dispatcher
 */
class Dispatcher
{
    // Stores a list of routes that determine how to map a URI:
    protected $_routes = array();
    
    // Stores the name of the error controller that will handle 
    // dispatching errors:
    protected $_errorController = "ErrorController";
    
    // Stores a reference to the Request object for this request:
    protected $_request = null;
    
    // Stores a reference to the Response object for this transaction:
    protected $_response = null;
    
    // Stores a reference to the Controller that will handle this requet:
    protected $_controller = null;
    
    // Stores a reference to the View that will render the page:
    protected $_view = null;
    
    /**
     *
     */
    protected $_preDispatchCallbacks = array();
    
    /**
     *
     */
    protected $_postDispatchCallbacks = array();


    /**
     * Sets up a custom route to match a URI against when dispatching the URI. A custom route
     * is simply run by running a preg_match against the URI, and rewriting it to the 
     * replacement on a valid match using preg_replace. 
     * 
     * @argument string The pattern to match against
     * @argument string The string to rewrite to
     * @returns void
     */
    public function addRoute($pattern, $replacement)
    {
        $this->_routes[] = array(
            "pattern" => $pattern,
            "replace" => $replacement
        );
    
    } // addRoute()


    /**
     * Returns the Request object for the current request
     * 
     * @returns Request The current Request object
     */
    public function &getRequest()
    {
        return $this->_request;
        
    } // getRequest()


    /**
     * Returns the Request object for the current request
     * 
     * @returns Request The current Request object
     */
    public function &getResponse()
    {
        return $this->_response;
        
    } // getRequest()
    

    /**
     * Routes the specified request to an associated controller and action (class and method). 
     * Loads any specified view file stored in the controller and passes along any data stored
     * in the controller. 
     * 
     * This method checks for custom routes first (see AddRoute()), before checking for standard
     * routes. A standard route is a UI formed as follows /[controllername]/[methodname] where
     * [controllername] is the name of the controller, without the word "controller"
     * 
     * A standard controller is named like: ExampleController. To invoke the index method of 
     * this controller, one would navigate to /example/index.
     * 
     * The data loaded via the controller's SetData() method is exploded and available for the
     * view file.                                    
     * 
     * @argument string The current request URI
     * @returns void
     */
    public function connect($requestUri)
    {
        $this->_request = new Request();
        $this->_request->_uri = $requestUri;
        
        $this->_response = new Response();
        
        $controller = "";
        $action = "";
        $arguments = array();
        
        // Loop each stored route and attempt to find a match to the URI:
        
        foreach($this->_routes as $route)
        {               
            if(preg_match($route["pattern"], $requestUri) != 0)
            {
                $requestUri = preg_replace($route["pattern"], $route["replace"], $requestUri);
            }
        }
        
        if(substr($requestUri, 0, 1) == "/")
        {
            $requestUri = substr($requestUri, 1);
        }
        
        $this->_request->_rewrittenUri = $requestUri;
        
        
        // Explode the request on /
        $request = explode("/", $requestUri);
        
        // normalize the controller -
        // example action_items should become ActionItemsController
        
        $controllerName = count($request) > 0 ? array_shift($request) : "";
        
        $controllerName = ucwords(str_replace(array("-", "_"), " ", $controllerName));
        $controllerName = str_replace(" ", "", $controllerName);
        
        $this->_request->_controllerName = $controllerName . "Controller";
        
        $this->_request->_actionName = count($request) > 0 ? 
            str_replace("-", "_", array_shift($request)) : "index";
            
        $this->_request->_arguments = !empty($request) ? $request : array();
        
        $this->preDispatch();
        
        // If we've found a controller and the class exists:
        if(!empty($this->_request->_controllerName) && 
            class_exists($this->_request->_controllerName, true))
        {
            // Replace our empty controller with the routed one:                
            $this->_controller = new $this->_request->_controllerName($this);
            
            $this->_view = $this->_controller->getView();
            
            // Attempt to invoke an action on this controller:              
            $this->invokeAction();
        }
        else
        {
            // If we can't find the controller, we must throw an error:
            return $this->handleError(ErrorHandler::CONTROLLER_NOT_FOUND);
        }
        
        $this->postDispatch();
        
        // If this is an AJAX request, let's be assumptious and disable
        // the layout:
        // TODO move this logic to the view
        if($this->_request->isAjaxRequest())
        {
            $this->_view->disableLayout();
        }
        
        // Continue on with the view loader method which will put the appropriate presentation
        // on the screen:
        
        try
        {
            $this->_response->sendHeaders();
            $this->_view->renderPage();
        }
        catch(Exception $e)
        {
            $this->handleError($e->getMessage());
        }
        
        return $this->_request;
    
    } // connect()
    
    
    /**
     * Registers a preDispatch callback with this dispatcher. Callbacks
     * are called in the order they are registered.
     *
     * @param callback $callback The callback to invoke 
     * @return void
     */
    public function registerPreDispatchMethod($callback)
    {
        $this->_preDispatchCallbacks[] = $callback;
        
    } // registerPreDispatchMethod()
    
    
    /**
     * Registers a postDispatch callback with this dispatcher. Callbacks
     * are called in the order they are registered.
     *
     * @param callback $callback The callback to invoke
     * @return void
     */
    public function registerPostDispatchMethod($callback)
    {
        $this->_postDispatchCallbacks[] = $callback;
        
    } // registerPreDispatchMethod()
    
    
    /**
     * The preDispatch loop which calls each registered preDispatch
     * callback in the order they were registered. Passed to the callback
     * is a reference to this dispatcher.
     *
     * @return void
     */
    public function preDispatch()
    {
        foreach($this->_preDispatchCallbacks as $callback)
        {
            $dispatcher = &$this;
            
            if(is_callable($callback))
            {
                call_user_func_array($callback, array($dispatcher));
            }
        }
        
    } // preDispatch()
    
    
    /**
     * The postDispatch loop which calls each registered postDispatch
     * callback in the order they were registered. Passed to the callback
     * is a reference to this dispatcher.
     *
     * @return void
     */
    public function postDispatch()
    {
        foreach($this->_postDispatchCallbacks as $callback)
        {
            $dispatcher = &$this;
            
            if(is_callable($callback))
            {
                call_user_func_array($callback, array($dispatcher));
            }
        }
        
    } // postDispatch()
    
    
    /**
     * Called from connect(), responsible for calling the method of the controller
     * routed from the URI
     * 
     * @returns void
     */
    protected function invokeAction()
    {
        // is_callable() is used over method_exists() in order to properly utilize __call()
        
        if(!empty($this->_request->_actionName) && 
            is_callable(array($this->_controller, $this->_request->_actionName)))
        {
            // Call $oController->$sAction() with arguments $aArguments:
            call_user_func_array(array($this->_controller, $this->_request->_actionName), 
                $this->_request->_arguments);
        }
        else if(empty($this->_request->_actionName))
        {
            // Default to the index file:
            $this->_controller->index();
        }
        else
        {
            // Action is not callable, throw an error:
            return $this->handleError(ErrorHandler::ACTION_NOT_FOUND);
        }
    
    } // invokeAction()
    
    
    /**
     * Handles errors occurring during the Dispatch process and passes them off to the 
     * defined ErrorController, or throws an exception if the controller does not exist.
     * 
     * @returns void
     */
    public function handleError($errorCode)
    {
        if(!empty($this->_errorController) && 
            class_exists($this->_errorController, true))
        {
            $oController = new $this->_errorController($this);
            $oController->error($errorCode);
        }
        else
        {
            throw new Exception("No ErrorController configured; cannot handle error");
        }
        
    } // handleError()
    
    
    /**
     * Called to handle a 404 error
     * 
     * @deprecated Use handleError(ErrorHandler::FILE_NOT_FOUND);
     * @returns void
     */
    protected function invoke404Error()
    {
        if(!headers_sent())
        {
            header("HTTP/1.0 404 Not Found", true, 404);
        }
        
        $this->handleError(ErrorHandler::FILE_NOT_FOUND);
    
    } // Invoke404Error()
    
} // Dispatcher()

?>
