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
class ZFDebug_Controller_Plugin_Debug_Plugin_File extends ZFDebug_Controller_Plugin_Debug_Plugin
    implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $identifier = 'file';

    /**
     * Base path of this application
     * String is used to strip it from filenames
     *
     * @var string
     */
    protected $basePath;

    /**
     * Stores included files
     *
     * @var array
     */
    protected $includedFiles = null;

    /**
     * Stores names of used extension libraries
     *
     * @var array
     */
    protected $library;

    /**
     * Setting Options
     *
     * basePath:
     * This will normally not your document root of your webserver, its your
     * application root directory with /application, /library and /public
     *
     * library:
     * Your own library extension(s)
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        isset($options['base_path']) || $options['base_path'] = $_SERVER['DOCUMENT_ROOT'];
        isset($options['library']) || $options['library'] = null;
        
        $this->basePath = realpath($options['base_path']);
        is_array($options['library']) || $options['library'] = array($options['library']);
        $this->library = array_merge($options['library'], array('Zend', 'ZFDebug'));
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
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADPSURBVCjPdZFNCsIwEEZHPYdSz1DaHsMzuPM6RRcewSO4caPQ3sBDKCK02p+08DmZtGkKlQ+GhHm8MBmiFQUU2ng0B7khClTdQqdBiX1Ma1qMgbDlxh0XnJHiit2JNq5HgAo3KEx7BFAM/PMI0CDB2KNvh1gjHZBi8OR448GnAkeNDEDvKZDh2Xl4cBcwtcKXkZdYLJBYwCCFPDRpMEjNyKcDPC4RbXuPiWKkNABPOuNhItegz0pGFkD+y3p0s48DDB43dU7+eLWes3gdn5Y/LD9Y6skuWXcAAAAASUVORK5CYII=';
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        return count($this->getIncludedFiles()) . ' Files';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $linebreak = $this->getLinebreak();
        $included = $this->getIncludedFiles();
        $html = '<h4>' . count($included).' files included worth ';
        $size = 0;
        foreach ($included as $file) {
            $size += (int)@filesize($file);
        }
        $html .= round($size/1024, 1).'K</h4>';

        $libraryFiles = array();
        foreach ($this->library as $key => $value) {
            if ('' != $value) {
                $libraryFiles[$key] = '<h4>' . $value . ' Files</h4>';
            }
        }

        $html .= '<h4>Application Files</h4>';
        foreach ($included as $file) {
            $file = str_replace($this->basePath, '', $file);
            $filePaths = explode(DIRECTORY_SEPARATOR, $file);
            $inUserLib = false;
            foreach ($this->library as $key => $library) {
                if ('' != $library && in_array($library, $filePaths)) {
                    $libraryFiles[$key] .= $file . $linebreak;
                    $inUserLib = true;
                }
            }
            if (!$inUserLib) {
                $html .= $file .$linebreak;
            }
        }

        $html .= implode('', $libraryFiles);

        return $html;
    }

    /**
     * Gets included files
     *
     * @return array
     */
    protected function getIncludedFiles()
    {
        if (null !== $this->includedFiles) {
            return $this->includedFiles;
        }

        $this->includedFiles = get_included_files();
        sort($this->includedFiles);
        return $this->includedFiles;
    }
}
