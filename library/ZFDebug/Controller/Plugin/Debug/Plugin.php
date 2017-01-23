<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id: $
 */

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug_Plugin
{
    /**
     * @var string|null
     */
    protected $closingBracket = null;

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
    public function getIconData()
    {
        $icon = ZFDebug_Controller_Plugin_Debug::PUBLIC_DIR . '/img/unknown.png';

        return 'data: ' . mime_content_type($icon) . ';base64,' . base64_encode(file_get_contents($icon));
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

    /**
     * Transforms data into readable format
     *
     * @param array $values
     * @return string
     */
    protected function cleanData($values)
    {
        $linebreak = $this->getLinebreak();

        if (is_array($values)) {
            ksort($values);
        }
        $retVal = '<div class="pre">';
        foreach ($values as $key => $value) {
            $key = htmlspecialchars($key);
            if (is_numeric($value)) {
                $retVal .= $key . ' => ' . $value . $linebreak;
            } else {
                if (is_string($value)) {
                    $retVal .= $key . ' => \'' . htmlspecialchars($value) . '\'' . $linebreak;
                } else {
                    if (is_array($value)) {
                        $retVal .= $key . ' => ' . self::cleanData($value);
                    } else {
                        if (is_object($value)) {
                            $retVal .= $key . ' => ' . get_class($value) . ' Object()' . $linebreak;
                        } else {
                            if (is_null($value)) {
                                $retVal .= $key . ' => NULL' . $linebreak;
                            }
                        }
                    }
                }
            }
        }
        return $retVal . '</div>';
    }
}
