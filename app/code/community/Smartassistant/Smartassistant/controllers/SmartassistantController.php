<?php

class Smartassistant_Smartassistant_SmartassistantController extends Mage_Core_Controller_Front_Action
{
    /**
     * Run cron action
     * - if executed with GET: ?force=1 it will ignore configuration cron enable/disable settings
     */
    public function cronAction()
    {
        $force = Mage::app()->getRequest()->getParam('force', false);
        $generator = Mage::getModel('smartassistant/generator');
        $generator->scheduleRun($force);
    }
}
