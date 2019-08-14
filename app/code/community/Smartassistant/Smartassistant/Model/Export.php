<?php

class Smartassistant_Smartassistant_Model_Export extends Mage_Core_Model_Abstract
{
    /**
     * @var array Cache of product collection
     */
    private static $_productCollectionIteratorCached;

    /**
     * @var string Export file path - cache
     */
    private $_filePath;

    protected $_defaultFields;

    public function _construct()
    {
        parent::_construct();
        $this->_init('smartassistant/export');
        $this->_defaultFields = array(
            'name' => array(
                'fieldname' => 'name',
                'attribute_code' => 'name',
                'enabled' => '1',
                'when_empty' => '',
            ),
            'price' => array(
                'fieldname' => 'price',
                'attribute_code' => 'price',
                'enabled' => '1',
                'when_empty' => '',
            ),
            'offerurl' => array(
                'fieldname' => 'offerurl',
                'attribute_code' => 'url',
                'enabled' => '1',
                'when_empty' => Mage::getBaseUrl(),

            ),
            'picture' => array(
                'fieldname' => 'picture',
                'attribute_code' => 'image',
                'enabled' => '1',
                'when_empty' => rtrim(Mage::getBaseUrl('skin'), '/') . '/frontend/base/default/images/smartassistant/logo.png',
            ),
        );
    }

    /**
     * Receive days as array
     * @return array
     */
    public function getDays()
    {
        return explode(',', $this->getData('days'));
    }

    /**
     * Receive hours as array
     * @return array
     */
    public function getHours()
    {
        return explode(',', $this->getData('hours'));
    }

    /**
     * Set days
     * @param array $values
     */
    public function setDays($values)
    {
        $this->setData('days', implode(',', $values));
    }

    /**
     * Set hours
     * @param array $values
     */
    public function setHours($values)
    {
        $this->setData('hours', implode(',', $values));
    }

    /**
     * Receive map of product attributes to export
     * @param true $onlyEnabled If true it will return only active fields
     * @return Smartassistant_Smartassistant_Model_Mysql4_Exportfield_Collection
     */
    public function getMap($onlyEnabled = false)
    {
        if ($this->getId()) {
            $collection = Mage::getModel('smartassistant/exportfield')->getCollection()
                ->addFieldToFilter('export_id', $this->getId());

            if ($onlyEnabled) {
                $collection->addFieldToFilter('enabled', true);
            }

            $collection->setOrder('position','ASC');
        } else {
            $collection = new Varien_Data_Collection();
            foreach ($this->_defaultFields as $field) {
                $fieldModel = Mage::getModel('smartassistant/exportfield');
                foreach ($field as $key => $value) {
                    $setter = 'set' . uc_words($key, '');
                    $fieldModel->$setter($value);
                }
                $collection->addItem($fieldModel);
            }
        }

        return $collection;
    }

    /**
     * Retrieve ids of fielsd
     *
     * @return array
     */
    public function getMapIds()
    {
        $collection = $this->getMap();
        $ids = array();
        foreach ($collection as $item) {
            $ids[] = $item->getId();
        }

        return $ids;
    }

    /**
     * Create field model
     *
     * @return Smartassistant_Smartassistant_Model_Exportfield
     */
    public function getMapModel()
    {
        return Mage::getModel('smartassistant/exportfield');
    }

    /**
     * Save fields map in database
     * 
     * @param array $map
     * @return boolean
     */
    public function assignFieldsMap($map)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        try {
            $connection->beginTransaction();

            $position = 1;
            $ids = $this->getMapIds();
            $idsToRemove = array_diff($ids, array_filter($map['id']));

            if (count($idsToRemove) > 0) {
                $collection = Mage::getModel('smartassistant/exportfield')->getCollection()
                    ->addFieldToFilter('id', array('in' => $idsToRemove));
                foreach ($collection as $itemToRemove) {
                    $itemToRemove->delete();
                }
            }
            

            foreach ($map['id'] as $key => $value) {

                /**
                 * If item should be removed we do not saving it
                 */
                if (in_array($value, $idsToRemove)) {
                    continue;
                }

                $model = Mage::getModel('smartassistant/exportfield');
                $itemData = array(
                    'export_id' => $this->getId(),
                    'fieldname' => $map['fieldname'][$key],
                    'attribute_code' => $map['attribute_code'][$key],
                    'enabled' => (boolean)$map['enabled'][$key],
                    'when_empty' => $map['when_empty'][$key],
                    'position' => $position++,
                );
                if (! empty($value)) {
                    $itemData['id'] = $value;
                }
                $model->setData($itemData);
                if ($model->validate($itemData)) {
                    $model->save();
                }
            }

            $connection->commit();
            return true;
        } catch (Smartassistant_Smartassistant_Helper_Exception $e) {
            $connection->rollback();
            return false;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
    }

    /**
     * Save rules
     *
     * @param array $conditions
     */
    public function saveRules($conditions)
    {
        $rule = $this->getRuleModel();
        $rule->loadPost(array(
            'conditions' => $conditions,
            'export_id' => $this->getId(),
        ));
        $rule->save();
    }

    /**
     * Check if export file exists for this configuration
     *
     * @return boolean
     */
    public function exportExists()
    {
        return file_exists($this->getFilePath());
    }

    /**
     * Retrieve las export time
     * @return type
     */
    public function lastExportTime()
    {
        if (! $this->exportExists()) {
            return null;
        }

        return filemtime($this->getFilePath());
    }

    /**
     * Retrieve products collection for this export
     *
     * @return Smartassistant_Smartassistant_Model_Core_CollectionIterator
     */
    public function getProductsCollection()
    {
        if ($this->_productCollectionIteratorCached === null) {
            $rules = $this->getRuleModel();
            $storeViewId = $this->getStoreId();
            $storeId = Mage::app()->getStore($storeViewId)->getGroupId();
            $rules->setCurrentWebsiteId($storeId);
            $ids = $rules->getMatchingProductIds();

            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->setStoreId($storeViewId);
            $collection->addFieldToFilter('entity_id', array('in' => $ids));

            $this->_productCollectionIteratorCached = new Smartassistant_Smartassistant_Model_Core_CollectionIterator($collection, null, $storeViewId);
        }

        return $this->_productCollectionIteratorCached;
    }

    /**
     * Get rule model used in this configuration or create new rule model
     * @return Smartassistant_Smartassistant_Model_Rule
     */
    public function getRuleModel()
    {
        if ($this->isEmpty()) {
            return Mage::getModel('smartassistant/rule');
        }

        $rule = Mage::getModel('smartassistant/rule')->load($this->getId(), 'export_id');
        if ($rule->isEmpty()) {
            return Mage::getModel('smartassistant/rule');
        }

        return $rule;
    }

    /**
     * Get export file path associated with tis configuration
     * @return string
     */
    public function getFilePath()
    {
        if ($this->_filePath === null) {
            $exportsDir = Mage::helper('smartassistant/filesystem')->getExportsDir();

            $filename = 'export-' . $this->getId() . '.csv';

            $this->_filePath = rtrim($exportsDir, DS) . DS . $filename;
        }

        return $this->_filePath;
    }

    /**
     * Cascade remove configuration with all related data
     *
     * @return boolean
     */
    public function cascadeDelete()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $fields = $this->getMap();
        $rule = $this->getRuleModel();

        try {
            $connection->beginTransaction();

            $rule->delete();

            foreach ($fields as $field) {
                $field->delete();
            }
            
            $this->delete();

            @unlink($this->getFilePath());

            $connection->commit();
        } catch (Smartassistant_Smartassistant_Helper_Exception $e) {
            $connection->rollback();
            return false;
        }

        return true;
    }

    /**
     * Get CSV delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        return ';';
    }

    /**
     * Get CSV enclosure
     *
     * @return string
     */
    public function getEnclosure()
    {
        return '"';
    }

    /**
     * Check if ftp server asotiated with this configuration is valid
     *
     * @return boolean
     */
    public function isFtpValid()
    {
        $storeId = $this->getStoreId();

        $host = Mage::getStoreConfig('smartassistant/ftp/host', $storeId);
        $login = Mage::getStoreConfig('smartassistant/ftp/user', $storeId);
        $password = Mage::getStoreConfig('smartassistant/ftp/password', $storeId);
        $pasive = Mage::getStoreConfig('smartassistant/ftp/pasivemode', $storeId);
        $transferMode = Smartassistant_Smartassistant_Helper_FtpConnector::BINARY;
        $port = Mage::getStoreConfig('smartassistant/ftp/port', $storeId);
        $port = empty($port) ? 21 : $port;

        if (empty($host) || empty($login) || empty($password) || empty($transferMode) || empty($port)) {
            return false;
        }

        $ftp = new Smartassistant_Smartassistant_Helper_FtpConnector($host, $login, $password, $port, $pasive);
        $success = $ftp->ping();

        return $success;
    }

    public function getLastTaskId()
    {
        $collection = Mage::getModel('smartassistant/task_config')->getCollection();
        $collection->addFieldToFilter('export_id', $this->getId());
        $select = $collection->getSelect();
        $select->order('id DESC');
        $select->limit(1);
        if ($collection->getSize() <= 0) {
            return null;
        }
        $item = $collection->getFirstItem();
        $id = $item->getTaskId();
        return $id;
    }
}
