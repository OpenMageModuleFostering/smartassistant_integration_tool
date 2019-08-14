<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Export_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $collection = Mage::getModel('smartassistant/export')->getCollection();
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
        $this->addColumn('name', array(
            'header' => Mage::helper('smartassistant')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));
        $this->addColumn('filename', array(
            'header' => Mage::helper('smartassistant')->__('Filename'),
            'align' => 'left',
            'index' => 'filename',
        ));

        $this->addColumn('store_id', array(
            'header' => Mage::helper('smartassistant')->__('Website'),
            'index' => 'store_id',
            'type' => 'store',
            'width' => '100px',
            'store_view'=> true,
            'display_deleted' => true,
        ));


        $this->addColumn('active', array(
            'header' => Mage::helper('smartassistant')->__('Is active'),
            'align' => 'left',
            'index' => 'active',
            'type' => 'options',
            'options' => array(
                '0' => Mage::helper('smartassistant')->__('No'),
                '1' => Mage::helper('smartassistant')->__('Yes'),
            ),
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
                    'caption' => Mage::helper('smartassistant')->__('Edit'),
                    'url'     => array(
                        'base'=>'*/*/edit',
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
