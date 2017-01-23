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
 */

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug_Plugin_Time extends Zend_Controller_Plugin_Abstract
    implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $identifier = 'time';

    /**
     * @var Zend_Log
     */
    protected $logger;

    /**
     * @var array
     */
    protected $timer = [
        'dispatchLoopStartup'  => 0,
        'dispatchLoopShutdown' => 0
    ];

    /**
     * @var string|null
     */
    protected $closingBracket = null;

    /**
     * Creating time plugin
     */
    public function __construct()
    {
        Zend_Controller_Front::getInstance()->registerPlugin($this);
    }

    /**
     * Get the ZFDebug logger
     *
     * @return Zend_Log
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = Zend_Controller_Front::getInstance()
                ->getPlugin('ZFDebug_Controller_Plugin_Debug')
                ->getPlugin('Log')
                ->getLog();
            $this->logger->addPriority('Time', 9);
        }
        return $this->logger;
    }

    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns the base64 encoded icon
     *
     * @return string
     **/
    public function getIconData()
    {
        $icon = ZFDebug_Controller_Plugin_Debug::PUBLIC_DIR . '/img/time.png';

        return 'data: ' . mime_content_type($icon) . ';base64,' . base64_encode(file_get_contents($icon));
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        return round($this->timer['dispatchLoopShutdown'] - $this->timer['dispatchLoopStartup'], 2) . 'ms';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        return '';
    }

    /**
     * @param float $value
     * @return string
     */
    public function format($value)
    {
        return round($value, 2) . 'ms';
    }

    /**
     * Sets a time mark identified with $name
     *
     * @param string $name
     * @deprecated Use ZFDebug_Controller_Plugin_Debug_Plugin_Log
     */
    public function mark($name)
    {
        $this->getLogger()->mark("$name");
        trigger_error("ZFDebug Time plugin is deprecated, use the Log plugin");
    }

    public function getDispatchStatistics()
    {
        if (!Zend_Session::isStarted()) {
            Zend_Session::start();
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $this_module = $request->getModuleName();
        $this_controller = $request->getControllerName();
        $this_action = $request->getActionName();

        $timerNamespace = new Zend_Session_Namespace('ZFDebug_Time', false);
        $timerNamespace->data[$this_module][$this_controller][$this_action][]
            = round($this->timer['dispatchLoopShutdown'], 2);

        // Limit to last 10 requests
        while (10 < count($timerNamespace->data[$this_module][$this_controller][$this_action])) {
            array_shift($timerNamespace->data[$this_module][$this_controller][$this_action]);
        }
        $stats = '';
        foreach ($timerNamespace->data as $module => $controller) {
            if ($module != $this_module) {
                continue;
            }
            foreach ($controller as $con => $action) {
                if ($con != $this_controller) {
                    continue;
                }
                foreach ($action as $key => $data) {
                    if ($key != $this_action) {
                        continue;
                    }
                    $stats = ' – avg ' . $this->calcAvg($data) . 'ms/' . count($data) . ' requests';
                    $stats .= 'Min: ' . round(min($data), 2) . ' ms'.$this->getLinebreak();
                    $stats .= 'Max: ' . round(max($data), 2) . ' ms'.$this->getLinebreak();
                }
            }
        }
        return $stats;
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $this->timer['dispatchLoopStartup'] = (microtime(true) - $_SERVER['REQUEST_TIME']) * 1000;
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function dispatchLoopShutdown()
    {
        $this->timer['dispatchLoopShutdown'] = (microtime(true) - $_SERVER['REQUEST_TIME']) * 1000;
    }

    /**
     * Calculate average time from $array
     *
     * @param array $array
     * @param int $precision
     * @return float
     */
    protected function calcAvg(array $array, $precision = 2)
    {
        if (!is_array($array)) {
            return 'ERROR in method calcAvg(): this is a not array';
        }

        foreach ($array as $value) {
            if (!is_numeric($value)) {
                return 'N/A';
            }
        }

        return round(array_sum($array) / count($array), $precision);
    }

    /**
     * @return string
     */
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

    /**
     * @return bool
     */
    protected function isXhtml()
    {
        /** @var Zend_Controller_Action_Helper_ViewRenderer $viewRenderer */
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        /** @var Zend_View $view */
        $view = $viewRenderer->view;

        return is_null($view) ? false : $view->doctype()->isXhtml();
    }
}
