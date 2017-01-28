<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id$
 *
 * @method Zend_Controller_Request_Http getRequest()
 */
class ZFDebug_Controller_Plugin_Debug extends Zend_Controller_Plugin_Abstract
{
    const PUBLIC_DIR = __DIR__ . "/../../../../public";

    /**
     * Contains registered plugins
     *
     * @var array
     */
    protected $plugins = [];

    /**
     * Contains options to change Debug Bar behavior
     */
    protected $options = [
        'plugins'   => [
            'Variables' => null,
            'Time'      => null,
            'Memory'    => null
        ],
        'imagePath' => null
    ];

    /**
     * Standard plugins
     *
     * @var array
     */
    public static $standardPlugins = [
        'Cache',
        'Html',
        'Database',
        'Exception',
        'File',
        'Memory',
        'Time',
        'Variables',
        'Log'
    ];

    /**
     * Debug Bar Version Number
     * for internal use only
     *
     * @var string
     */
    protected $version = '1.6';

    /**
     * Creates a new instance of the Debug Bar
     *
     * @param array|Zend_Config $options
     * @throws Zend_Controller_Exception
     * @return void
     */

    protected $closingBracket = null;

    public function __construct($options = null)
    {
        if (isset($options)) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            }

            /**
             * Verify that adapter parameters are in an array.
             */
            if (!is_array($options)) {
                throw new Zend_Exception('Debug parameters must be in an array or a Zend_Config object');
            }

            $this->setOptions($options);
        }

        /**
         * Creating ZF Version Tab always shown
         */
        $version = new ZFDebug_Controller_Plugin_Debug_Plugin_Text();
        $version->setPanel($this->getVersionPanel())
            ->setTab($this->getVersionTab())
            ->setIdentifier('copyright')
            ->setIconData('');
        $this->registerPlugin($version);

        /**
         * Creating the log tab
         */
        $logger = new ZFDebug_Controller_Plugin_Debug_Plugin_Log();
        $this->registerPlugin($logger);
        $logger->mark('Startup - ZFDebug construct()', true);

        /**
         * Loading already defined plugins
         */
        $this->loadPlugins();
    }

    /**
     * Get the ZFDebug logger
     *
     * @return Zend_Log
     */
    public function getLogger()
    {
        return $this->getPlugin('Log')->logger();
    }

    /**
     * Sets options of the Debug Bar
     *
     * @param array $options
     * @return ZFDebug_Controller_Plugin_Debug
     */
    public function setOptions(array $options = [])
    {
        if (isset($options['imagePath'])) {
            $this->options['imagePath'] = $options['imagePath'];
        }

        if (isset($options['plugins'])) {
            $this->options['plugins'] = $options['plugins'];
        }
        return $this;
    }

    /**
     * Register a new plugin in the Debug Bar
     *
     * @param ZFDebug_Controller_Plugin_Debug_Plugin_Interface
     * @return ZFDebug_Controller_Plugin_Debug
     */
    public function registerPlugin(ZFDebug_Controller_Plugin_Debug_Plugin_Interface $plugin)
    {
        $this->plugins[$plugin->getIdentifier()] = $plugin;
        return $this;
    }

    /**
     * UnRegister a plugin in the Debug Bar
     *
     * @param string $plugin
     * @return ZFDebug_Controller_Plugin_Debug
     */
    public function unRegisterPlugin($plugin)
    {
        if (false !== strpos($plugin, '_')) {
            foreach ($this->plugins as $key => $_plugin) {
                if ($plugin == get_class($_plugin)) {
                    unset($this->plugins[$key]);
                }
            }
        } else {
            $plugin = strtolower($plugin);
            if (isset($this->plugins[$plugin])) {
                unset($this->plugins[$plugin]);
            }
        }
        return $this;
    }

    /**
     * Get a registered plugin in the Debug Bar
     *
     * @param string $identifier
     * @return ZFDebug_Controller_Plugin_Debug_Plugin_Interface|false
     */
    public function getPlugin($identifier)
    {
        $identifier = strtolower($identifier);

        return isset($this->plugins[$identifier]) ? $this->plugins[$identifier] : false;
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     */
    public function dispatchLoopShutdown()
    {
        if ($this->getRequest()->isXmlHttpRequest()
            || Zend_Controller_Front::getInstance()->getRequest()->getParam('ZFDEBUG_DISABLE')) {
            return;
        }

        $html = "<div id='ZFDebug_info'>
                    <span class='ZFDebug_span special' onclick='ZFDebugPanel(\"collapse\");'>
                        <img class='align-middle' src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAABHElEQVQoFZ2SMUsDQRCFN6eRIIIS0MZW0gUs72orayvh/C3HNfkXV/kftEhz3V0pigghrc0VQdsYiO/b3MAaYgh58HZ2387czt6+jvuLvpaX4oV41m59KTbipzhrNdexieKVOBBPAy2cfmsxEaeIBwwCRdfiMYt/0JNOJ3NxFmmgPU7qii7P8yExRKCRQy41jsR7qITRUqiq6sk05mjsmaY45I43Ii14KPEhjuPbuq6fEWyeJMnjKsOPDYV34lEgOitG4wNrRchz7rgXDlXFO21tVR24tVOp2e/n8I4L8VzslWXZRFE0SdN0rLVHURSvaFmWvbUSRvgw55gB/Fu2CZvCj8QXcWrOwYM44kTEIZvASe+it5ydaIk7m/wXTbV0eSnRtrUAAAAASUVORK5CYII='>
                    </span>";

        /**
         * Creating panel content for all registered plugins
         */
        foreach ($this->plugins as $plugin) {
            if ($tab = $plugin->getTab()) {
                $icon = $this->options['imagePath'] . '/' . $plugin->getIdentifier() . '.png';
                $pluginIcon = ($this->options['imagePath'] && file_exists($icon)) ? $icon : $plugin->getIconData();

                /* @var $plugin ZFDebug_Controller_Plugin_Debug_Plugin_Interface */
                $showPanel = ($plugin->getPanel() == '') ? 'log' : $plugin->getIdentifier();

                $html .= "<span id='ZFDebugInfo_{$plugin->getIdentifier()}' class='ZFDebug_span clickable'
                                onclick='ZFDebugPanel(\"ZFDebug_$showPanel\");'>";
                if ($pluginIcon) {
                    $html .= "<img src='$pluginIcon' class='align-middle'>";
                }
                $html .= $tab . "</span>";
            }
        }

        $html .= '<span id="ZFDebugInfo_Request" class="ZFDebug_span">'
            . round(memory_get_peak_usage() / 1024) . 'K in '
            . round((microtime(true) - $_SERVER['REQUEST_TIME']) * 1000) . 'ms</span></div>'
            . '<div id="ZFDebugResize"></div>';

        /**
         * Creating menu tab for all registered plugins
         */
        $this->getPlugin('log')->mark('Shutdown', true);
        foreach ($this->plugins as $plugin) {
            if ($panel = $plugin->getPanel()) {
                /* @var $plugin ZFDebug_Controller_Plugin_Debug_Plugin_Interface */
                $html .= "<div id='ZFDebug_{$plugin->getIdentifier()}' class='ZFDebug_panel'>$panel</div>";
            }
        }

        $this->output($html);
    }

    /**
     * Load plugins set in config option
     */
    protected function loadPlugins()
    {
        foreach ($this->options['plugins'] as $plugin => $options) {
            if (is_numeric($plugin)) {
                $plugin = $options;
                $options = [];
            }

            if (is_a($plugin, 'ZFDebug_Controller_Plugin_Debug_Plugin_Interface')) {
                $this->registerPlugin($plugin);
                continue;
            }

            if (!is_string($plugin)) {
                throw new Exception("Invalid plugin name", 1);
            }
            $plugin = ucfirst($plugin);

            // Register a className
            if (in_array($plugin, ZFDebug_Controller_Plugin_Debug::$standardPlugins)) {
                // standard plugin
                $pluginClass = 'ZFDebug_Controller_Plugin_Debug_Plugin_' . $plugin;
            } else {
                // we use a custom plugin
                if (!preg_match('~^[\w]+$~D', $plugin)) {
                    throw new Zend_Exception("ZFDebug: Invalid plugin name [$plugin]");
                }
                $pluginClass = $plugin;
            }

            $object = new $pluginClass($options);
            $this->registerPlugin($object);
        }
    }

    /**
     * Return version tab
     *
     * @return string
     */
    protected function getVersionTab()
    {
        return '<strong>ZFDebug</strong>';
    }

    /**
     * Returns version panel
     *
     * @return string
     */
    protected function getVersionPanel()
    {
        $panel = '<h4>Zend Framework ' . Zend_Version::VERSION . ' / PHP ' . phpversion() . ' with extensions:</h4>';
        $extensions = get_loaded_extensions();
        natcasesort($extensions);
        $panel .= implode('<br>', $extensions);
        return $panel;
    }

    /**
     * Returns path to the specific icon
     *
     * @param string $kind
     * @return string
     */
    protected function icon($kind)
    {
        $filePath = $this->options['imagePath'] . '/' . $kind . '.png';
        return file_exists($filePath) ? $filePath : $this->options['imagePath'] . '/unknown.png';
    }

    /**
     * Returns html header for the Debug Bar
     *
     * @return string
     */
    protected function headerOutput()
    {
        $collapsed = isset($_COOKIE['ZFDebugCollapsed']) ? $_COOKIE['ZFDebugCollapsed'] : '';
        $boxHeight = isset($_COOKIE['ZFDebugPanelCollapsed']) ? '32' : ($collapsed ? '32' : '240');
        $panelSize = ($boxHeight - 50) . 'px';

        return "<style type='text/css'>
                    #ZFDebug_offset {height: $boxHeight px;}
                    #ZFDebug {height: $boxHeight px;}
                    #ZFDebug .ZFDebug_panel {height: $panelSize;}" .
                    file_get_contents(self::PUBLIC_DIR . "/css/debug.css") . "
                </style>
                <script type='text/javascript'>
                    var collapsed = '{$collapsed}';
                    collapsed = collapsed !== '' ? collapsed : false;
                    window.onload = function () {
                        window.zfdebugHeight = '$boxHeight';
                    }
                    " . file_get_contents(self::PUBLIC_DIR . "/js/debug.js") . "</script>";
    }

    /**
     * Appends Debug Bar html output to the original page
     *
     * @param string $html
     * @return void
     */
    protected function output($html)
    {
        $class = isset($_COOKIE['ZFDebugPanelCollapsed']) ? 'collapsed' : '';
        $html = "<div id='ZFDebug_offset'></div><div id='ZFDebug' class='$class'>$html</div></body>";
        $response = $this->getResponse();
        $response->setBody(str_ireplace('</body>', $this->headerOutput() . $html, $response->getBody()));
    }

    public function getLinebreak()
    {
        return '<br' . $this->getClosingBracket();
    }

    /**
     * @return string
     */
    public function getClosingBracket()
    {
        if (!$this->closingBracket) {
            $this->closingBracket = $this->isXhtml() ? ' />' : '>';
        }

        return $this->closingBracket;
    }

    protected function isXhtml()
    {
        if ($view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view) {
            $doctype = $view->doctype();
            return $doctype->isXhtml();
        }
        return false;
    }
}
