<?php

    require("../library/openavanti/Application.php");
    
    class SkeletonApplication extends OpenAvanti\Application
    {
        
        /**
         *
         *
         */
        public function init()
        {
            $this->appendIncludePath("../");
            //$this->addAdditionalAutoloadPaths(array("../application"));
            $this->addAdditionalAutoloadPaths(array("../library/skeleton"));
            
            $this->initializeSession();
            $this->initializeEnvironment();
            
            $this->initializeMenus();
            
            // Tell the Dispatcher to run the authenticate() method before routing the URI
            
            $this->getDispatcher()->registerPreDispatchMethod(array($this, 'authenticate'));
            
            OpenAvanti\Database::addProfile('default', array(
                "driver" => "postgres",
                "name" => "skeleton",
                "user" => "postgres",
                "password" => ""
            ));
        
        } // init()
        
        
        /**
         *
         *
         */
        protected function initializeSession()
        {
            session_start();
            
        } // initializeSession()
        
        
        /**
         *
         *
         */
        protected function initializeEnvironment()
        {
            // We're going to store all dates in the database as GMT and localize for each user
            
            date_default_timezone_set("GMT");
            
            OpenAvanti\View::setDefaultLayout("default.phtml");
            OpenAvanti\View::setViewFileExtension(".phtml");
            
        } // initializeEnvironment()
        
        
        /**
         *
         *
         */
        public function initializeMenus()
        {
            $adminMenu = new MenuContainer();
            $adminMenu->addItems(array(
                new MenuItem('Roles', '/roles'),
                new MenuItem('Users', '/users'),
            ));
            
            $mainMenu = new MenuContainer();
            $mainMenu->addItems(array(
                new MenuItem('Menu #1', '#'),
                new MenuItem('Menu #2', '#'),
                new MenuItem('Menu #3', '#'),
                new MenuItem('Menu #4', '#')
            ));
            
            OpenAvanti\Registry::store('adminMenu', $adminMenu);
            OpenAvanti\Registry::store('mainMenu', $mainMenu);
            
        } // initializeMenus()
        
        
        /**
         *
         *
         */
        public function authenticate(&$dispatcher)
        {
            $request = &$dispatcher->getRequest();
            
            Authenticator::authenticate($request);
            
        } // authenticate()
        
        
    } // SkeletonApplication()

?>
