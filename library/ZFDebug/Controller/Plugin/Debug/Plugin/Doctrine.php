<?php

/**
 * ZFDebug Doctrine ORM plugin
 * Enable it at the configuration step of ZFDebug
 * (http://code.google.com/p/zfdebug/wiki/Installation)
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 */
class ZFDebug_Controller_Plugin_Debug_Plugin_Doctrine extends ZFDebug_Controller_Plugin_Debug_Plugin
    implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $identifier = 'doctrine';

    /**
     * @var array Doctrine connection profiler that will listen to events
     */
    protected $profilers = [];

    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Variables
     *
     * @param Doctrine_Manager|array $options
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['manager']) || !count($options['manager'])) {
            if (\Doctrine_Manager::getInstance()) {
                $options['manager'] = Doctrine_Manager::getInstance();
            }
        }

        foreach ($options['manager']->getIterator() as $connection) {
            $this->profilers[$connection->getName()] = new Doctrine_Connection_Profiler();
            $connection->addListener($this->profilers[$connection->getName()]);
        }
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
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        if (!$this->profilers) {
            return 'No Profiler';
        }

        foreach ($this->profilers as $profiler) {
            $queries = 0;
            $time = 0;
            foreach ($profiler as $event) {
                if (in_array($event->getCode(), $this->getQueryEventCodes())) {
                    $time += $event->getElapsedSecs();
                    $queries += 1;
                }
            }
            $profilerInfo[] = $queries . ' in ' . round($time * 1000, 2) . ' ms';
        }
        $html = implode(' / ', $profilerInfo);

        return $html;
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        if (!$this->profilers) {
            return '';
        }

        $html = '<h4>Database queries</h4>';

        foreach ($this->profilers as $name => $profiler) {
            $html .= '<h4>Connection: ' . $name . '</h4>';

            if (count($profiler) > 0) {
                $html .= '<ol>';

                foreach ($profiler as $event) {
                    if (in_array($event->getCode(), $this->getQueryEventCodes())) {
                        $query = htmlspecialchars($event->getQuery());
                        $time = round($event->getElapsedSecs() * 1000, 2);
                        $html .= '<li>' . $query . '<p><strong>Time: </strong>' . $time . ' ms</p></li>';
                    }
                }

                $html .= '</ol>';
            }
        }

        return $html;
    }

    /**
     * return codes for 'query' type of event
     */
    protected function getQueryEventCodes()
    {
        return [
            Doctrine_Event::CONN_EXEC,
            Doctrine_Event::STMT_EXECUTE,
            Doctrine_Event::CONN_QUERY,
        ];
    }

    /**
     * Returns the base64 encoded icon
     * Doctrine Icon will be used if you're using ZFDebug > 1.5
     * icon taken from: http://code.google.com/p/zfdebug/issues/detail?id=20
     *
     * @return string
     **/
    public function getIconData()
    {
        $icon = ZFDebug_Controller_Plugin_Debug::PUBLIC_DIR . '/img/doctrine.svg';

        return 'data: ' . mime_content_type($icon) . ';base64,' . base64_encode(file_get_contents($icon));
    }
}
