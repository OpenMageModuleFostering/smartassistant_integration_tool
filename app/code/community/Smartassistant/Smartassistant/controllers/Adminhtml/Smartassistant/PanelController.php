<?php

class Smartassistant_Smartassistant_Adminhtml_Smartassistant_PanelController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        if ($this->isAllowed() && ($url=$this->getPanelLink()) !== null) {
            $this->getResponse()->setRedirect($url);
            return;
        }

        Mage::getSingleton('core/session')->addError(Mage::helper('smartassistant')->__('Unable to redirect'));
        $this->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl('/'));
    }

    private function isAllowed()
    {
        $accountId = Mage::getStoreConfig('smartassistant/auth/account_id');
        $accountPassword = Mage::getStoreConfig('smartassistant/auth/account_password');
        $oemUser = Mage::getSingleton('admin/session')->getUser()->getUsername();

        return (! empty($accountId) && ! empty($accountPassword) && ! empty($oemUser));
    }

    private function encrypt($input)
    {
        $originalPath = get_include_path();
        $file = dirname(__FILE__) . '/../../../secret/oem.public.key';
        $publicKey = file_get_contents($file);

        set_include_path(rtrim(Mage::getBaseDir('lib'), '/') . '/phpseclib');
        $rsa = new Crypt_RSA();
        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
        $rsa->loadKey($publicKey);
        $ciphertext = $rsa->encrypt($input);
        $ciphertext = base64_encode($ciphertext);

        set_include_path($originalPath);
        return $ciphertext;
    }

    private function getPanelLink()
    {
        $accountId = Mage::getStoreConfig('smartassistant/auth/account_id');
        $accountPassword = Mage::getStoreConfig('smartassistant/auth/account_password');
        $oemUser = Mage::getSingleton('admin/session')->getUser()->getUsername();
        $platformUrl = Mage::getStoreConfig('smartassistant/advisor/platformUrl');

        if (empty($accountId) || empty($accountPassword) || empty($oemUser)) {
            return null;
        }

        $oem = $this->encrypt(json_encode(array(
            'accountId' => $accountId,
            'accountPassword' => $accountPassword,
            'oemUser' => $oemUser,
            'oemPartner' => 'Magento',
        )));

        return $platformUrl . $oem;
    }

    protected function _isAllowed()
    {
        return true;
    }
}
