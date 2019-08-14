<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_Tab_Rules extends Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_BaseTab
{
    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $rules = $this->export()->getRuleModel();

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        $rules->getConditions()->setJsFormObject('rule_conditions_fieldset');

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl(
                $this->getUrl('*/*/newConditionHtml/form/rule_conditions_fieldset')
            );

        $fieldset = $form->addFieldset('conditions_fieldset', array(
            'legend'=>Mage::helper('smartassistant')->__('Conditions (leave blank for all products)'))
        )->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('smartassistant')->__('Conditions'),
            'title' => Mage::helper('smartassistant')->__('Conditions'),
            'required' => true,
        ))->setRule($rules)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
