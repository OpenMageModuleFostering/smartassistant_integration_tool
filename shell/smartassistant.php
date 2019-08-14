<?php

require_once 'abstract.php';

class Smartassistant_Shell_ExportCli extends Mage_Shell_Abstract
{
    private $_time = 36000;

    private $_classPointer = 'run';

    private $_paramsSeparator = ';';

    public function run()
    {
        set_time_limit($this->_time);

        if ($this->getArg($this->_classPointer)) {
            $parts = explode('/', $this->getArg($this->_classPointer));
            $type = array_shift($parts);
            $method = array_pop($parts);
            $class = implode('/', $parts);
            $args = $this->getArgs();
            $response = $this->fire($type, $class, $method, $args);
            die($response);
        } elseif ($this->getArg('ping')) {
            echo '1';
        } else {
            echo $this->usageHelp();
        }
    }

    private function fire($type, $class, $method, $args)
    {
        if ($type == 'model') {
            $object = Mage::getModel($class);
            $response = call_user_func_array(array($object, $method), $args);
        } elseif ($type == 'helper') {
            $object = Mage::helper($class);
            $response = call_user_func_array(array($object, $method), $args);
        } else {
            $response = false;
        }
        return $response;
    }

    public function usageHelp()
    {
        return <<<USAGE

Usage:  php -f smartassistant.php -- [options]

  --generate all      Generate all
  --generate <id>     Generate ID <id>

USAGE;
    }

    protected function getArgs()
    {
        $args = $this->_args;
        unset($args[$this->_classPointer]);
        foreach ($args as &$arg) {
            if (strpos($arg, $this->_paramsSeparator)) {
                $arg = explode($this->_paramsSeparator, $arg);
            }
        }
        return $args;
    }

    protected function _parseArgs()
    {
        $current = null;
        foreach ($_SERVER['argv'] as $arg) {
            $match = array();
            if (preg_match('#^--([\w\d_-]{1,})$#', $arg, $match) || preg_match('#^-([\w\d_]{1,})$#', $arg, $match)) {
                $current = $match[1];
                $this->_args[$current] = true;
            } else {
                if ($current) {
                    $this->_args[$current] = $arg;
                    $current = null;
                } else if (preg_match('#^([\w\d_]{1,})$#', $arg, $match)) {
                    $this->_args[$match[1]] = true;
                }
            }
        }
        return $this;
    }
}

$shell = new Smartassistant_Shell_ExportCli();
$shell->run();
