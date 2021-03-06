<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initZFDebug()
    {
        // Setup autoloader with namespace
        Zend_Loader_Autoloader::getInstance()->registerNamespace('ZFDebug');

        // Ensure the front controller is initialized
        $this->bootstrap('FrontController');

        // Retrieve the front controller from the bootstrap registry
        $front = $this->getResource('FrontController');

        // Only enable zfdebug if options have been specified for it
        if ($this->hasOption('zfdebug')) {
            // Create ZFDebug instance
            $zfDebug = new ZFDebug_Controller_Plugin_Debug($this->getOption('zfdebug'));

            // Register ZFDebug with the front controller
            $front->registerPlugin($zfDebug);
        }
//
//        Plugins that take objects as parameters like Database and Cache
//        need to be registered manually:
//
//        $zfDebug->registerPlugin(new ZFDebug_Controller_Plugin_Debug_Plugin_Database($db));
//
//        Alternative configuration without application.ini
//        $options = [
//            'plugins' => [
//                'variables',
//                'database',
//                'file' => ['basePath' => APPLICATION_PATH, 'myLibrary' => 'Scienta'],
//                'memory',
//                'time',
//                //'auth',
//                //'cache' => ['backend' => $cache->getBackend()],
//                'exception'
//            ]
//        ];
//        $zfDebug = new ZFDebug_Controller_Plugin_Debug($options);
//        Register ZFDebug with the front controller
//        $front->registerPlugin($zfDebug);
    }
}
