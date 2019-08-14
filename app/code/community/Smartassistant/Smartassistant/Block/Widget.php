<?php

class Smartassistant_Smartassistant_Block_Widget extends Mage_Core_Block_Abstract implements Mage_Widget_Block_Interface
{
    private $_containerId;

    public function isEnable()
    {
        $enable = Mage::getStoreConfig('smartassistant/advisor/enable');
        $path = $this->getContextPath();
        $code = $this->getCode();
        return ($enable && ! empty($path) && ! empty($code));
    }

    public function getContextPath()
    {
        return Mage::getStoreConfig('smartassistant/advisor/contextPath');
    }

    public function getCode()
    {
        return $this->getData('code');
    }

    public function getContainerId()
    {
        if ($this->_containerId === null) {
            $this->_containerId = 'advisor-container-' . substr($this->getNameInLayout(), strlen('ANONYMUS_') + 1);
        }

        return $this->_containerId;
    }

    public function getJs()
    {
        $js = '<script type="text/javascript">
if(SmartAssistant){
    smrt42_jquery(function() {
        SmartAssistant.integrate({
            "divId" : "'.$this->getContainerId().'",
            "advisorContextPath" : "'.$this->getContextPath().'",
            "advisorCode" : "'.$this->getCode().'",
            "disableTracking" : false
        });
    });
}
</script>';
        return $js;
    }

    protected function _toHtml()
    {
        if (! $this->isEnable()) {
            return '';
        }

        $layout = $this->getLayout();
        $body = $layout->getBlock('before_body_end');
        $body->append($layout->createBlock('core/text', $this->getContainerId())->setText($this->getJs()));
        return '<div id="' . $this->getContainerId() . '"></div>';
    }
}
