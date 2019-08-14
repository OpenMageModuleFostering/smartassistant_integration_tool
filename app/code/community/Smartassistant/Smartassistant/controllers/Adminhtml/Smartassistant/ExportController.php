<?php

class Smartassistant_Smartassistant_Adminhtml_Smartassistant_ExportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Display grid
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('catalog');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('smartassistant/adminhtml_export'));
        $this->renderLayout();
    }

    /**
     * Display task stats
     */
    public function statsAction()
    {
        $id = Mage::app()->getRequest()->getParam('id', null);
        if (
            ($model = $this->loadCurrentModelInstance($id, false)) !== null
            && ($taskId =$model->getLastTaskId())!==null
        ) {
            $this->loadLayout();
            $block = $this->getLayout()->createBlock('smartassistant/adminhtml_taskStats');
            $block->setTaskId($taskId);
            $this->getResponse()->clearHeaders();

            $task = Mage::getModel('smartassistant/task')->load($taskId);
            $finished = in_array($task->status_id, array(
                Smartassistant_Smartassistant_Model_Task_Status::FINISHED,
                Smartassistant_Smartassistant_Model_Task_Status::FAILED,
            ));

            $response = array(
                'finished' => $finished,
                'html' => $block->toHtml(),
            );
            $this->getResponse()
                ->clearHeaders()
                ->setHeader('Content-Type', 'application/json')
                ->setBody(json_encode($response));
        }
    }

    /**
     * Display config edit form
     */
    public function editAction()
    {
        if (Mage::app()->getRequest()->isPost()) {
            try {
                $this->processPost();
                return;
            } catch (Smartassistant_Smartassistant_Helper_Exception $e) {
                die($e->getMessage());
            }
        }

        $this->_title($this->__('smartassistans'))->_title($this->__('Feed configuration'));

        $this->loadLayout();

        $head = $this->getLayout()->getBlock('head');
        $head->setCanLoadExtJs(true);
        $head->setCanLoadExtJs(true);
        $head->setCanLoadRulesJs(true);

        $head->addItem('js', 'mage/adminhtml/rules.js');
        $head->addItem('skin_js', 'smartassistant/smartassistant.js');
        $head->addItem('skin_css', 'smartassistant/smartassistant.css');

        $this->_setActiveMenu('catalog');
        $this->_addContent($this->getLayout()->createBlock('smartassistant/adminhtml_export_edit'))
            ->_addLeft($this->getLayout()->createBlock('smartassistant/adminhtml_export_edit_tabs'));
        $this->renderLayout();
    }

    /**
     * Delete configuration
     */
    public function deleteAction()
    {
        $id = Mage::app()->getRequest()->getParam('id', null);
        try {
            if ($id === null) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Id is invalid')
                );
            }
            if (($model = $this->loadCurrentModelInstance($id, false)) === null) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Unable to find model for given id')
                );
            }
            if (! $model->cascadeDelete()) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Unable remove this model')
                );
            }

            Mage::getSingleton('core/session')->addSuccess(Mage::helper('smartassistant')->__('Success'));
            $this->redirect();
        } catch (Smartassistant_Smartassistant_Helper_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->redirect($id);
        }
    }

    /**
     * Send generated file to ftp
     */
    public function sendAction()
    {
        $id = Mage::app()->getRequest()->getParam('id', null);
        try {
            if ($id === null) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Id is invalid')
                );
            }
            if (($model = $this->loadCurrentModelInstance($id, false)) === null) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Unable to find model for given id')
                );
            }
            if (! $model->exportExists()) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Unable to find export for given configuration')
                );
            }
            if (! $model->isFtpValid()) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Unable to connect with FTP server. Check access FTP configuration')
                );
            }

            $generator = Mage::getModel('smartassistant/generator');
            $generator->customRun($model->getId(), false, true);

            Mage::getSingleton('core/session')->addSuccess(Mage::helper('smartassistant')->__('Success'));
            $this->redirect($id, true);
        } catch (Smartassistant_Smartassistant_Helper_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->redirect($id, true);
        }
    }

    /**
     * Generate file
     */
    public function generateAction()
    {
        $id = Mage::app()->getRequest()->getParam('id', null);
        try {
            if ($id === null) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Id is invalid')
                );
            }
            if (($model = $this->loadCurrentModelInstance($id, false)) === null) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Unable to find model for given id')
                );
            }
            
            $generator = Mage::getModel('smartassistant/generator');
            $generator->customRun($model->getId(), true, false);

            Mage::getSingleton('core/session')->addSuccess(Mage::helper('smartassistant')->__('Success'));
            $this->redirect($id, true, true);
        } catch (Smartassistant_Smartassistant_Helper_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->redirect($id, true);
        }
    }

    /**
     * Download generated file
     */
    public function downloadAction()
    {
        $id = Mage::app()->getRequest()->getParam('id', null);
        try {
            if ($id === null) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Id is invalid')
                );
            }
            if (($model = $this->loadCurrentModelInstance($id, false)) === null) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Unable to find model for given id')
                );
            }
            if (! $model->exportExists()) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Unable to find export for given configuration')
                );
            }

            $path = $model->getFilePath();

            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . $model->getFilename() . ".csv\"");
            readfile($path);
            exit;

        } catch (Smartassistant_Smartassistant_Helper_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->redirect($id, true);
        }
    }

    /**
     * Generate file and send it to ftp server
     */
    public function generateSendAction()
    {
        $id = Mage::app()->getRequest()->getParam('id', null);
        try {
            if ($id === null) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Id is invalid')
                );
            }
            if (($model = $this->loadCurrentModelInstance($id, false)) === null) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Unable to find model for given id')
                );
            }
            if (! $model->isFtpValid()) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Unable to connect with FTP server. Check access FTP configuration')
                );
            }

            $generator = Mage::getModel('smartassistant/generator');
            $generator->customRun($model->getId(), true, true);

            Mage::getSingleton('core/session')->addSuccess(Mage::helper('smartassistant')->__('Success'));
            $this->redirect($id, true, true);
        } catch (Smartassistant_Smartassistant_Helper_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->redirect($id, true);
        }
    }

    /**
     * Display new condition html
     */
    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('catalogrule/rule'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * Process POST request qith config data and save it in database
     */
    private function processPost()
    {
        if (($configId = Mage::app()->getRequest()->getParam('id', null))!==null) {
            $model = Mage::getModel('smartassistant/export')->load($configId);
            if ($model->isEmpty()) {
                throw new Smartassistant_Smartassistant_Helper_Exception(
                    Mage::helper('smartassistant')->__('Unable to find model for given id')
                );
            }
        } else {
            /**
             * If there is no $configId provided we will create new config
             */
            $model = Mage::getModel('smartassistant/export');
        }

        $data = Mage::app()->getRequest()->getPost();

        /**
         * We need to assign boolean options directly to provide properly action
         */
        $data['autogenerate'] = (boolean) Mage::app()->getRequest()->getPost('autogenerate', false);
        $data['autosend'] = (boolean) Mage::app()->getRequest()->getPost('autosend', false);

        $generalData = array_intersect_key($data, array_fill_keys(array(
            'store_id', 'active', 'name', 'filename', 'autogenerate', 'autosend',
        ), null));
        $model->setData(array_merge($generalData, array('id' => $model->getId())));
        
        /**
         * We need to assign options directly via setter methods in Smartassistant_Smartassistant_Model_Export
         */
        $model->setDays(isset($data['days']) ? $data['days'] : array());
        $model->setHours(isset($data['hours']) ? $data['hours'] : array());

        $model->save();
        $model->assignFieldsMap($data['map']);
        $model->saveRules($data['rule']['conditions']);

        Mage::getSingleton('core/session')->addSuccess(Mage::helper('smartassistant')->__('Success'));
        $this->redirect($model->getId());
    }

    /**
     * Redirecting
     *
     * @param int $id Export ID
     * @param boolean $back Flag
     * @return void
     */
    private function redirect($id = null, $back = false, $runLoader = false)
    {
        if ($id !== null && ($back || $this->getRequest()->getParam('back'))) {
            $params = array('id' => $id);
            if ($runLoader) {
                $params['run'] = true;
            }
            if (($tab=Mage::app()->getRequest()->getParam('tab', null)) !== null) {
                $params['tab'] = $tab;
            }
            $this->getResponse()->setRedirect($this->getUrl('*/*/edit', $params));
        } else {
            $this->getResponse()->setRedirect($this->getUrl('*/*/index'));
        }
        return;
    }

    /**
     * Load model class for given ID or create new model (if $createNew is true)
     *
     * @param int $id
     * @param boolean $createNew
     * @param string $modelClass
     * @return mixed
     */
    private function loadCurrentModelInstance($id, $createNew = true, $modelClass = 'smartassistant/export')
    {
        $model = Mage::getModel($modelClass)->load($id);
        if ($model->isEmpty() && $createNew) {
            return Mage::getModel($modelClass);
        } elseif (! $model->isEmpty()) {
            return $model;
        } else {
            return null;
        }
    }

    protected function _isAllowed()
    {
        return true;
        return parent::_isAllowed();
    }
}
