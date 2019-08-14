<?php

class Smartassistant_Smartassistant_Helper_CliRunner extends Mage_Core_Helper_Abstract
{
    /**
     * @var string Type: model
     */
    const MODEL = 'model';

    /**
     * @var string Type: helper
     */
    const HELPER = 'helper';

    /**
     * @var string Shell script location
     */
    protected $_shellScript;

    /**
     * @var string PHP Cli command
     */
    protected $_phpCli;

    /**
     * @var string Stript name
     */
    protected $_scriptFilename = 'smartassistant.php';

    /**
     * @var string Separator of arrays in commendline
     */
    protected $_argsSeparator = ';';

    public function __construct()
    {
        $this->_phpCli = $this->getPhpCliCommand();
        $this->_shellScript = Mage::getBaseDir() . DS . 'shell' . DS . $this->_scriptFilename;
    }

    /**
     * Retrieve php command and check if shell is available
     *
     * @return string
     */
    public function ping()
    {
        $cmd = $this->_phpCli . ' ' . $this->_shellScript . ' --ping';
        $result = array();
        @exec($cmd, $result);
        return (implode(' ', $result) === '1');
    }

    /**
     * Run CLI command and execute specified model/helper method
     *
     * @return string
     */
    public function run($type, $class, $method, $params)
    {
        $params = $this->prepareParamsString($params);
        $type .= '/' . $class . '/' . $method;

        $cmd = $this->_phpCli . ' ' . $this->_shellScript . ' --run ' . $type . ' ' . $params;
        $cmd .= ' 1>/dev/null 2>&1 &';

        return exec($cmd);
    }

    /**
     * Prepare params string
     *
     * @return string
     */
    private function prepareParamsString($params)
    {
        foreach ($params as $name => &$value) {
            if (is_array($value)) {
                $value = implode($this->_argsSeparator, $value);
            }
            $value = '--' . $name . ' ' . $value;
        }
        $params = implode(' ', $params);
        return $params;
    }

    /**
     * Retrieve php command
     *
     * @return string
     */
    private function getPhpCliCommand()
    {
        $phpBin = 'php';

        if (defined('PHP_BINDIR')) {
            $phpBin = PHP_BINDIR . DS . $phpBin;
        }

        return $phpBin;
    }
}
