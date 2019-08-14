<?php

class Smartassistant_Smartassistant_Model_Core_CollectionIterator implements Iterator, Countable
{
    private $_primaryKey;

    private $_varienDbSelect;

    private $_splitedSelect;

    private $_originalCollection;

    private $_collection;

    private $_pick = 5000;

    private $_connection;
    
    private $_current;

    private $_items = array();

    private $_count;

    public function __construct($collection, $varienDbSelect = null)
    {
        $this->_varienDbSelect = ($varienDbSelect !== null ? $varienDbSelect : $collection->getSelect());
        $this->_originalCollection = $collection;
        $this->_connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->_splitedSelect = $this->splitSelect($this->_varienDbSelect, '*', $this->_pick);
    }

    /**
     * Split select query
     *
     * @param Varien_Db_Select $select
     * @param string $entityIdField
     * @param int $step
     * @return array
     */
    private function splitSelect(Varien_Db_Select $select, $entityIdField = '*', $step = 10000)
    {
        $countSelect = clone $select;

        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->columns('COUNT(' . $entityIdField . ')');

        $row = $this->_connection->fetchRow($countSelect);
        $totalRows = array_shift($row);

        $bunches = array();
        for ($i = 0; $i <= $totalRows; $i += $step) {
            $bunchSelect = clone $select;
            $bunches[] = $bunchSelect->limit($step, $i);
        }

        return $bunches;
    }

    public function setPick($pick)
    {
        $this->_pick = $pick;
    }

    public function getPick()
    {
        return $this->_pick;
    }

    private function getSize()
    {
        $countSelect = clone $this->_varienDbSelect;
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->columns('COUNT(*)');
        $row = $this->_connection->fetchRow($countSelect);
        $totalRows = array_shift($row);
        return $totalRows;
    }

    public function loadNextPart()
    {
        if (($select = current($this->_splitedSelect)) === false) {
            $this->_current = null;
            return;
        }

        next($this->_splitedSelect);

        $collection = clone $this->_originalCollection;
        $ids = $this->_connection->fetchCol($select);

        $collection->getSelect()->reset(Zend_Db_Select::WHERE);
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', array( 'in' => $ids ));

        if ($collection->getSize() <= 0) {
            $this->_current = null;
            return;
        }

        $this->_items = $collection->getItems();
        $this->_current = key($this->_items);
    }


    /**
     * FROM Iterator, Countable
     */

    /**
     * Pobiera ilosc elementow
     */
    public function count($mode = 'COUNT_NORMAL')
    {
        if ($this->_count === null) {
            $this->_count = $this->getSize();
        }
        return $this->_count;
    }

    /**
     * Pobiera aktualny obiekt
     */
    public function current()
    {
        return $this->_items[$this->_current];
    }

    /**
     * Pobiera aktualny klucz
     */
    public function key()
    {
        return $this->_current;
    }

    /**
     * Przewijamy do nastepnego
     */
    public function next()
    {
        if (next($this->_items) === false) {
            $this->loadNextPart();    
        } else {
            $this->_current = key($this->_items);
        }
    }

    /**
     * Przewijamy do poczatku
     */
    public function rewind()
    {
        reset($this->_splitedSelect);
        $this->loadNextPart();
    }

    public function valid()
    {
        return ($this->_current !== null);
    }

}
