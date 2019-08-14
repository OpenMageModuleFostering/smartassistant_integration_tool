<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Tasks_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('smarassistant_export_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $taskConfigTable = Mage::getSingleton('core/resource')->getTableName('smartassistant/task_config');
        $taskStatusTable = Mage::getSingleton('core/resource')->getTableName('smartassistant/task_status');

        $collection = Mage::getModel('smartassistant/task')->getCollection();
        $select = $collection->getSelect();
        $select->join(array("ts" => $taskStatusTable), "main_table.status_id = ts.id", array('task_status' => 'ts.name'));
        $select->join(array("tc" => $taskConfigTable), "main_table.id = tc.task_id", array('configs_amount' => 'count(tc.id)'));
        $select->group(array(
            'main_table.id', 'main_table.time', 'ts.name', 'ts.name'
        ));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('smartassistant')->__('ID'),
            'align' => 'right',
            'index' => 'id',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('smartassistant')->__('Status'),
            'align' => 'left',
            'index' => 'task_status',
        ));

        $this->addColumn('configs_amount', array(
            'header' => Mage::helper('smartassistant')->__('Configs amount'),
            'align' => 'right',
            'index' => 'configs_amount',
        ));

        $this->addColumn('time', array(
            'header' => Mage::helper('smartassistant')->__('Time'),
            'align' => 'right',
            'index' => 'time',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('smartassistant')->__('Action'),
            'align' => 'left',
            'type' => 'action',
            'filter' => false,
            'sortable' => false,
            'getter' => 'getId',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('smartassistant')->__('Preview'),
                    'url'     => array(
                        'base'=>'*/*/preview',
                    ),
                    'field'   => 'id'
                )
            ),
            'is_system' => true
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'id' => $row->getId()
        ));
    }
}
