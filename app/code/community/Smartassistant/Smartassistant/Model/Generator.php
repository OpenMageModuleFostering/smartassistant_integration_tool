<?php

class Smartassistant_Smartassistant_Model_Generator extends Varien_Object
{
    /**
     * Run task by cron
     *
     * @param boolean $force Ignore config andforce to run task even when cron is disabled in configuration
     * @return mixed
     */
    public function scheduleRun($force = false)
    {
        if (! $force && ! Mage::getStoreConfig('smartassistant/general/cron')) {
            return;
        }

        $day = Mage::getSingleton('core/date')->date('w');
        $hour = Mage::getSingleton('core/date')->date('G');
        $minutes = floor(Mage::getSingleton('core/date')->date('i') / 30) == 1 ? 30 : 0;
        $time = ($hour * 60) + $minutes;
        
        $collection = Mage::getModel('smartassistant/export')->getCollection();
        $configIds = array();
        foreach ($collection as $export) {
            if ($this->shouldRunCron($export, $day, $time)) {
                $configIds[] = $export->getId();
            }
        }

        if (count($configIds) < 1) {
            return;
        }

        $task = Mage::getModel('smartassistant/task');
        $task->configure($configIds, null, null, $this->getTimeString($day, $time));
        return $this->run($task->getId());
    }

    /**
     * Custom run task
     *
     * @param array $configIds Array of config ids
     * @param boolean $generate Should generate file
     * @param boolean $send Should send file to FTP
     * @return mixed
     * @throws Smartassistant_Smartassistant_Helper_Exception
     */
    public function customRun($configIds, $generate, $send)
    {
        $task = Mage::getModel('smartassistant/task');
        $task->configure($configIds, $generate, $send);
        $useShell = Mage::getStoreConfig('smartassistant/general/useShell');

        if ($useShell) {
            $runner = Mage::helper('smartassistant/cliRunner');
            if (! $runner->ping()) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Unable to run task. Check if exec() function is enabled on your environment')
                );
            }
            $type = Smartassistant_Smartassistant_Helper_CliRunner::MODEL;
            $class = 'smartassistant/generator';
            $method = 'run';
            $params = array(
                'taskId' => $task->getId(),
            );
            return $runner->run($type, $class, $method, $params);
        } else {
            return $this->run($task->getId());
        }
    }

    /**
     * Run task execution
     *
     * @param int $taskId Id of task to run
     */
    public function run($taskId)
    {
        $task = Mage::getModel('smartassistant/task')->load($taskId);
        if(! $task->isEmpty()){
            $task->run();
        }
    }

    /**
     * Create date/time string
     *
     * @param string $day
     * @param string $time
     * @return string Date/time string
     */
    private function getTimeString($day, $time)
    {
        $timeString = Mage::getSingleton('core/date')->date('Ymd').$day.$time;
        return $timeString;
    }

    /**
     * Check if we need to run cron actions for provided config
     *
     * @param Smartassistant_Smartassistant_Model_Export $config
     * @param string $day
     * @param string $time
     * @return boolean
     */
    private function shouldRunCron($config, $day, $time)
    {
        $configDays = $config->getDays();
        $configHours = $config->getHours();

        /**
         * Check if date / time is enable in configuration
         */
        if (! in_array($day, $configDays) || !in_array($time, $configHours)) {
            return false;
        }

        /**
         * Check if we have task for this configuration in this date / time
         */
        $collection = Mage::getModel('smartassistant/task')->getCollection();
        $select = $collection->getSelect();
        $select->join(
            array('tc' => 'smartassistant_task_config'),
            'main_table.id=tc.task_id',
            array('export_id' => 'tc.export_id')
        );
        $collection->addFieldToFilter('export_id', $config->getId());
        $collection->addFieldToFilter('time', $this->getTimeString($day, $time));

        return ($collection->getSize() <= 0);
    }
}
