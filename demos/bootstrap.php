<?php
/**
 * Defining bootstrap for Zend Framework pre-1.8
 */

// Leave 'Database' options empty to rely on Zend_Db_Table default adapter
$options = [
    'plugins' => [
        'Variables',
        'Html',
        'Database' => ['adapter' => ['standard' => $db]],
        'File'     => ['basePath' => 'path/to/application/root'],
        'Memory',
        'Time',
        'Cache'    => ['backend' => $cache->getBackend()],
        'Exception'
    ]
];

$debug = new ZFDebug_Controller_Plugin_Debug($options);
$frontController->registerPlugin($debug);

// Alternative registration of plugins, also possible elsewhere in dispatch process
// $zfDebug = Zend_Controller_Front::getInstance()->getPlugin('ZFDebug_Controller_Plugin_Debug');
// $zfDebug->registerPlugin(new ZFDebug_Controller_Plugin_Debug_Plugin_Database($optionsArray));
