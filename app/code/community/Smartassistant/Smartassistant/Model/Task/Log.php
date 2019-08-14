<?php

class Smartassistant_Smartassistant_Model_Task_Log extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('smartassistant/task_log');
    }

    public function put($message, $task, $taskConfig)
    {
        $log = Mage::getModel('smartassistant/task_log');
        $log->setMessage($message);
        $log->setTaskId($task->getId());
        if ($taskConfig !== null) {
            $log->setTaskConfigId($taskConfig->getId());
        }
        $log->save();
    }
}
