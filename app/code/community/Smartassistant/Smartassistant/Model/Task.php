<?php

class Smartassistant_Smartassistant_Model_Task extends Mage_Core_Model_Abstract
{
    private $_summaryProgress = 0;
    private $_progresRefreshFrequency = 5;
    private $_taskConfigCached = array();
    private $_taskLog = null;
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('smartassistant/task');
    }

    public function configure($configIds, $generate=null, $send=null, $time = null)
    {
        if ($this->isState(array(
            Smartassistant_Smartassistant_Model_Task_Status::PROCESS,
            Smartassistant_Smartassistant_Model_Task_Status::FINISHED,
        ))) {
            return false;
        }

        $this->setProgress(0);
        $this->setItems(null);
        $this->setTime($time);
        $this->save();

        $configIds = is_array($configIds) ? $configIds : array($configIds);

        $configs = Mage::getModel('smartassistant/export')->getCollection()
            ->addFieldToFilter('id', array('in' => $configIds));

        foreach ($configs as $config) {
            $generate = $generate !== null ? $generate : $config->getAutogenerate();
            $send = $send !== null ? $send : $config->getAutosend();
            $taskConfig = $this->getTaskConfig($config->getId());
            $taskConfig->setGenerate($generate);
            $taskConfig->setSend($send);
            $taskConfig->save();
            $this->updateState(Smartassistant_Smartassistant_Model_Task_Status::WAITING, $taskConfig);
        }

        $this->updateState(Smartassistant_Smartassistant_Model_Task_Status::WAITING);

        return true;
    }

    public function isConfigured()
    {
        return true;
    }

    public function run()
    {
        if (! $this->isConfigured()) {
            die('asd');
        }

        $this->updateState(Smartassistant_Smartassistant_Model_Task_Status::INITIALIZE);

        /**
         * Some initialize operations
         */

        $this->updateState(Smartassistant_Smartassistant_Model_Task_Status::PROCESS);
        while (($taskConfig = $this->getNextTaskConfig()) !== null) {
            $this->updateState(Smartassistant_Smartassistant_Model_Task_Status::INITIALIZE, $taskConfig);

            $amount = $taskConfig->getConfig()->getProductsCollection()->count();
            $this->updateProgress(0, $amount, $taskConfig);

            $this->updateState(Smartassistant_Smartassistant_Model_Task_Status::PROCESS, $taskConfig);
            if ($taskConfig->getGenerate()) {
                $this->generate($taskConfig);
                $this->updateProgress($amount, $amount, $taskConfig);
            }
            if ($taskConfig->getSend()) {
                $this->sendFtp($taskConfig);
            }

            $this->updateState(Smartassistant_Smartassistant_Model_Task_Status::FINISHED, $taskConfig);
        }
        $this->updateState(Smartassistant_Smartassistant_Model_Task_Status::FINISHED);
    }

    private function getTaskConfig($configId)
    {
        if (! isset($this->_taskConfigCached[$configId])) {
            $taskConfig = Mage::getModel('smartassistant/task_config')->getCollection()
                ->addFieldToFilter('export_id', $configId)
                ->addFieldToFilter('task_id', $this->getId())
                ->getFirstItem();
            if($taskConfig->isEmpty()) {
                $taskConfig = Mage::getModel('smartassistant/task_config');
                $taskConfig->setExportId($configId);
                $taskConfig->setTaskId($this->getId());
                $taskConfig->save();
                $this->_taskConfigCached[$configId] = $taskConfig;
            }
        }
        return $this->_taskConfigCached[$configId];
    }

    private function setTaskConfig($taskConfig)
    {
        $taskConfig->save();
        $this->_taskConfigCached[$taskConfig->getExportId()] = $taskConfig;
    }

    private function getNextTaskConfig()
    {
        $taskConfig = Mage::getModel('smartassistant/task_config')->getCollection()
            ->addFieldToFilter('task_id', $this->getId())
            ->addFieldToFilter('status_id', Smartassistant_Smartassistant_Model_Task_Status::WAITING)
            ->getFirstItem();
        return $taskConfig->isEmpty() ? null : $taskConfig;
    }

    private function generate($taskConfig)
    {
        $message = Mage::helper('smartassistant')->__('Generation in process...');
        $this->log($message, $taskConfig);

        /**
         * @var Smartassistant_Smartassistant_Model_Export
         */
        $config = $taskConfig->getConfig();

        /**
         * @var Smartassistant_Smartassistant_Model_Mysql4_Exportfield_Collection
         */
        $map = $config->getMap(true);

        /**
         * @var Mage_Catalog_Model_Resource_Product_Collection
         */
        $productsCollection = $config->getProductsCollection();

        /**
         * @var int Amount of products to Export
         */
        $productsAmount = $productsCollection->count();

        $message = Mage::helper('smartassistant')->__('%s products were found', $productsAmount);
        $this->log($message, $taskConfig);

        /**
         * @var Smartassistant_Smartassistant_Helper_Attribute
         */
        $attributeHelper = Mage::helper('smartassistant/attribute');

        /**
         * @var Smartassistant_Smartassistant_Helper_Filesystem
         */
        $fileHelper = Mage::helper('smartassistant/filesystem');

        /**
         * csv configuration
         */
        $delimiter = $config->getDelimiter();
        $enclosure = $config->getEnclosure();

        /**
         * @var File path
         */
        $path = $config->getFilePath();

        /**
         * We need to remove old file (if exists)
         */
        if (file_exists($path)) {
            $message = Mage::helper('smartassistant')->__('Export file exists. It will be replaced');
            $this->log($message, $taskConfig);

            if (! unlink($path)) {
                $message = Mage::helper('smartassistant')->__('Can not remove export file. Generation stoped. Check file permissions to: %s', $path);
                $this->log($message, $taskConfig);
                return;
            }
        }

        /**
         * Openning file
         */
        $handler = $fileHelper->openFile($path);

        /**
         * Write headers to file
         */
        $headers = array();
        foreach ($map as $field) {
            $headers[] = $field->getFieldname();
        }
        $fileHelper->putCsvLine($handler, $headers, $delimiter, $enclosure);

        /**
         * Write product data
         */
        $counter = 1;
        foreach ($productsCollection as $product) {
            $productFields = array();
            foreach ($map as $field) {
                $value = $attributeHelper->getAttributeValue($product, $field);
                $productFields[] = $value;
            }
            $fileHelper->putCsvLine($handler, $productFields, $delimiter, $enclosure);
            if ($counter % $this->_progresRefreshFrequency == 0) {
                $this->updateProgress($counter, $productsAmount, $taskConfig);
            }
            $counter++;
        }

        /**
         * Close file
         */
        $fileHelper->closeFile($handler);
        $message = Mage::helper('smartassistant')->__('Generation is finished');
        $this->log($message, $taskConfig);
    }

    private function sendFtp($taskConfig)
    {
        $message = Mage::helper('smartassistant')->__('Sending file to FTP...');
        $this->log($message, $taskConfig);
        /**
         * @var Smartassistant_Smartassistant_Model_Export
         */
        $config = $taskConfig->getConfig();
        if(! $config->isFtpValid()) {
            $message = Mage::helper('smartassistant')->__('FTP server is unreachable');
            $this->log($message, $taskConfig);
        }
        if (! $config->exportExists()) {
            $message = Mage::helper('smartassistant')->__('Unable to find export for given configuration');
            $this->log($message, $taskConfig);
            return false;
        }

        /**
         * @var int Store ID
         */
        $storeId = $config->getStoreId();

        /**
         * Ftp configuration
         */
        $host = Mage::getStoreConfig('smartassistant/ftp/host', $storeId);
        $login = Mage::getStoreConfig('smartassistant/ftp/user', $storeId);
        $password = Mage::getStoreConfig('smartassistant/ftp/password', $storeId);
        $path = Mage::getStoreConfig('smartassistant/ftp/path', $storeId);
        $pasive = Mage::getStoreConfig('smartassistant/ftp/pasivemode', $storeId);
        $transferMode = Smartassistant_Smartassistant_Helper_FtpConnector::BINARY;
        $port = Mage::getStoreConfig('smartassistant/ftp/port', $storeId);
        $port = empty($port) ? 21 : $port;

        $destinationFileName = $config->getFilename() . '.csv';
        $sourceFilePath = $config->getFilePath();

        $ftp = new Smartassistant_Smartassistant_Helper_FtpConnector($host, $login, $password, $port, $pasive);
        $success = $ftp->putFile($destinationFileName, $sourceFilePath, $path, $transferMode);
        if (! $success) {
            $message = Mage::helper('smartassistant')->__('Unable to send file to FTP server. Check access permissions on server');
            $this->log($message, $taskConfig);
        } else {
            $message = Mage::helper('smartassistant')->__('File was sent to FTP');
            $this->log($message, $taskConfig);
        }

        return $success;
    }

    private function isState($status)
    {
        $status = is_array($status) ? $status : array($status);
        return in_array($this->getStatusId(), $status);
    }

    private function updateState($status, $taskConfig = null)
    {
        if ($taskConfig === null) {
            $this->setStatusId($status);
            $this->save();
        } else {
            $taskConfig->setStatusId($status);
            $this->setTaskConfig($taskConfig);
        }
    }

    private function updateProgress($progress, $all, $taskConfig = null)
    {
        $this->_summaryProgress += $progress;
        $this->setProgress($this->_summaryProgress);
        $this->save();

        if ($taskConfig !== null) {
            $taskConfig->setProgress($progress);
            $taskConfig->setItems($all);
            $this->setTaskConfig($taskConfig);
        }
    }

    private function log($message, $taskConfig = null)
    {
        if ($this->_taskLog === null) {
            $this->_taskLog = Mage::getModel('smartassistant/task_log');
        }
        $this->_taskLog->put($message, $this, $taskConfig);
    }

    public function getStats()
    {
        $configTable = Mage::getSingleton('core/resource')->getTableName('smartassistant/export');
        $taskConfigTable = Mage::getSingleton('core/resource')->getTableName('smartassistant/task_config');
        $taskStatusTable = Mage::getSingleton('core/resource')->getTableName('smartassistant/task_status');
        $taskLogTable = Mage::getSingleton('core/resource')->getTableName('smartassistant/task_log');

        $collection = Mage::getModel('smartassistant/task_log')->getCollection();
        
        $select = $collection->getSelect();
        $select->join(array("tc" => $taskConfigTable), "main_table.task_config_id = tc.id", array(
            'partial_progress' => 'tc.progress',
            'partial_amount' => 'tc.items',
        ));
        $select->join(array("tcs" => $taskStatusTable), "tc.status_id = tcs.id", array(
            'partial_status' => 'tcs.name'
        ));
        $select->join(array("ec" => $configTable), "tc.export_id = ec.id", array(
            'export_name' => 'ec.name'
        ));
        $select->order('main_table.id');

        $collection->addFieldToFilter('main_table.task_id', $this->getId());

        $out = array();
        $items = $collection->getItems();
        foreach ($items as $item) {
            if (! isset($out[$item['task_config_id']])) {
                $out[$item['task_config_id']] = array(
                    'log' => array(),
                    'status' => array(),
                );
            }
            $out[$item['task_config_id']]['log'][] = array(
                'datetime' => $item['datetime'],
                'message' => $item['message'],
            );
            $out[$item['task_config_id']]['status'] = array(
                'export_name' => $item['export_name'],
                'progress' => $item['partial_progress'],
                'amount' => $item['partial_amount'],
                'status' => $item['partial_status'],
            );
        }

        return $out;
    }

    public function getTaskConfigs()
    {
        $collection = Mage::getModel('smartassistant/task_config')->getCollection();
        $collection->addFieldToFilter('task_id', array('eq' => $this->getId()));
        return $collection;
    }

    public function getStatus()
    {
        $model = Mage::getModel('smartassistant/task_status')->load($this->getStatusId());
        return $model;
    }
}
