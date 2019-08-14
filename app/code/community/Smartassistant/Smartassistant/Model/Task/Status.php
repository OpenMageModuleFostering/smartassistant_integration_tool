<?php

class Smartassistant_Smartassistant_Model_Task_Status extends Mage_Core_Model_Abstract
{
    /**
     * @var int Status - waiting
     */
    const WAITING = 1;

    /**
     * @var int Status - initialize
     */
    const INITIALIZE = 2;

    /**
     * @var int Status - process
     */
    const PROCESS = 3;

    /**
     * @var int Status - finished with success
     */
    const FINISHED = 4;

    /**
     * @var int Status - file generated
     */
    const GENERATED = 5;

    /**
     * @var int Status - file send
     */
    const SEND = 6;

    /**
     * @var int Status - failed
     */
    const FAILED = 7;

    public function _construct()
    {
        parent::_construct();
        $this->_init('smartassistant/task_status');
    }
}
