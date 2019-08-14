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

    /**
     * @return string
     */
    public function getCss()
    {
        $css = <<<CSS
<link rel="stylesheet" type="text/css" href="//st.smartassistant.com/advisor-fe-web/css-design?advisorCode={$this->getCode()}" media="all" />
CSS;
        return $css;
    }

    public function getJs()
    {
        $js = <<<JS
<script src="//st.smartassistant.com/advisor-fe-web/assets/js-nwd/smartassistant.nwd.all.js"></script>
<script src="//st.smartassistant.com/advisor-fe-web/custom-javascript?advisorCode={$this->getCode()}"></script>
<script type="text/javascript">
if(SmartAssistant){
    smrt42_jquery(function() {
        SmartAssistant.integrate({
            "divId" : "{$this->getContainerId()}",
            "advisorContextPath" : "{$this->getContextPath()}",
            "advisorCode" : "{$this->getCode()}",
            "disableTracking" : false
        });
    });
}
</script>
JS;
        return $js;
    }

    protected function _toHtml()
    {
        if (! $this->isEnable()) {
            return '';
        }

        $layout = $this->getLayout();
        $body = $layout->getBlock('before_body_end');
        $body->append($layout->createBlock(
            'core/text',
            $this->getContainerId())->setText($this->getCss() . $this->getJs())
        );

        return '<div id="' . $this->getContainerId() . '"></div>';
    }
}
